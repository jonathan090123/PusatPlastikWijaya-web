<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AdminProductController extends Controller
{
    // (fetch) List produk dari tabel products dengan filter & search
    public function index(Request $request)
    {
        // (fetch) Query produk dari tabel products
        $query = Product::with('category');

        // (search) Cari produk dari tabel products
        if ($request->filled('search')) {
            $term = trim($request->search);
            // (search) Split search term into individual keywords (split on whitespace)
            $keywords = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);

            if (count($keywords) > 1) {
                // (search) Multi-keyword: each keyword must appear in name OR product_code
                $query->where(function ($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->where(function ($inner) use ($keyword) {
                            $inner->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('product_code', 'like', '%' . $keyword . '%');
                        });
                    }
                });
            } else {
                // (search) Single keyword: normal LIKE search
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%' . $term . '%')
                        ->orWhere('product_code', 'like', '%' . $term . '%');
                });
            }
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sort = $request->input('sort', 'newest');
        $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    // (fetch) Form tambah produk: fetch kategori dari tabel categories
    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    // (adm) Simpan produk baru ke tabel products + product_units
    public function store(Request $request)
    {
        // (val) Validasi input produk
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'nullable|string|max:50|unique:products,product_code',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'weight' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_alert' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'conversion_units' => 'nullable|array',
            'conversion_units.*.unit' => 'required|string|max:20',
            'conversion_units.*.conversion_value' => 'required|integer|min:1',
            'conversion_units.*.price' => 'required|numeric|min:0',
        ]);

        // (val) Pastikan satuan konversi tidak sama dengan satuan dasar dan tidak duplikat
        if ($request->filled('conversion_units')) {
            $baseUnit = $request->input('unit');
            $seenUnits = [];
            foreach ($request->conversion_units as $cu) {
                if (!empty($cu['unit'])) {
                    if ($cu['unit'] === $baseUnit) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Satuan konversi tidak boleh sama dengan satuan dasar (' . $baseUnit . ').');
                    }
                    if (in_array($cu['unit'], $seenUnits)) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Satuan konversi tidak boleh memiliki nama yang sama.');
                    }
                    $seenUnits[] = $cu['unit'];
                }
            }
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['discount_price'] = $request->filled('discount_price') ? ($request->price - $request->discount_price) : null;
        $validated['weight'] = $validated['weight'] ?? 0;

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        try {
            DB::beginTransaction();

            $product = Product::create($validated);

            // (adm) Simpan satuan konversi ke tabel product_units
            // Save satuan konversi
            if ($request->filled('conversion_units')) {
                foreach ($request->conversion_units as $cu) {
                    if (!empty($cu['unit']) && !empty($cu['conversion_value']) && isset($cu['price'])) {
                        $product->productUnits()->create([
                            'unit' => $cu['unit'],
                            'conversion_value' => (int) $cu['conversion_value'],
                            'price' => $cu['price'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil ditambahkan!');
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'product_units')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Satuan konversi tidak boleh memiliki nama yang sama.');
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan database. Silakan coba lagi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // (fetch) Form edit produk: fetch dari tabel products + product_units + categories
    public function edit(Product $product)
    {
        $product->load('productUnits');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // (adm) Update produk ke tabel products + product_units satuan
    public function update(Request $request, Product $product)
    {
        // (val) Validasi input produk
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'nullable|string|max:50|unique:products,product_code,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'weight' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_alert' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'conversion_units' => 'nullable|array',
            'conversion_units.*.unit' => 'required|string|max:20',
            'conversion_units.*.conversion_value' => 'required|integer|min:1',
            'conversion_units.*.price' => 'required|numeric|min:0',
        ]);

        // (val) Pastikan satuan konversi tidak sama dengan satuan dasar dan tidak duplikat
        if ($request->filled('conversion_units')) {
            $baseUnit = $request->input('unit');
            $seenUnits = [];
            foreach ($request->conversion_units as $cu) {
                if (!empty($cu['unit'])) {
                    if ($cu['unit'] === $baseUnit) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Satuan konversi tidak boleh sama dengan satuan dasar (' . $baseUnit . ').');
                    }
                    if (in_array($cu['unit'], $seenUnits)) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Satuan konversi tidak boleh memiliki nama yang sama.');
                    }
                    $seenUnits[] = $cu['unit'];
                }
            }
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['discount_price'] = $request->filled('discount_price') ? ($request->price - $request->discount_price) : null;
        $validated['weight'] = $validated['weight'] ?? 0;

        // (val) Pastikan slug unik
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('slug', $validated['slug'])->where('id', '!=', $product->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        try {
            DB::beginTransaction();

            $product->update($validated);

            // (adm) Update satuan konversi ke tabel product_units
            // Sync satuan konversi
            $product->productUnits()->delete();
            if ($request->filled('conversion_units')) {
                foreach ($request->conversion_units as $cu) {
                    if (!empty($cu['unit']) && !empty($cu['conversion_value']) && isset($cu['price'])) {
                        $product->productUnits()->create([
                            'unit' => $cu['unit'],
                            'conversion_value' => (int) $cu['conversion_value'],
                            'price' => $cu['price'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil diperbarui!');
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'product_units')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Satuan konversi tidak boleh memiliki nama yang sama.');
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan database. Silakan coba lagi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus!');
    }

    public function deleteImage(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
            $product->update(['image' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Gambar produk berhasil dihapus.']);
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $product->is_active,
            'message' => $product->is_active ? 'Produk diaktifkan' : 'Produk dinonaktifkan',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada produk dipilih.'], 422);
        }

        $products = Product::whereIn('id', $ids)->get();
        foreach ($products as $product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
        }

        return response()->json(['success' => true, 'message' => count($ids) . ' produk berhasil dihapus.']);
    }

    public function bulkToggle(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status'); // 'active' or 'inactive'
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada produk dipilih.'], 422);
        }

        Product::whereIn('id', $ids)->update(['is_active' => $status === 'active']);

        return response()->json(['success' => true, 'message' => count($ids) . ' produk diperbarui.']);
    }
}
