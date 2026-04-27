<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                  ->orWhere('product_code', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                              => 'required|string|max:255',
            'product_code'                      => 'nullable|string|max:50|unique:products,product_code',
            'category_id'                       => 'required|exists:categories,id',
            'description'                       => 'nullable|string',
            'unit'                              => 'required|string|max:20',
            'price'                             => 'required|numeric|min:0',
            'discount_price'                    => 'nullable|numeric|min:0|lt:price',
            'weight'                            => 'nullable|numeric|min:0',
            'stock'                             => 'required|integer|min:0',
            'stock_alert'                       => 'required|integer|min:0',
            'image'                             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'                         => 'boolean',
            'conversion_units'                  => 'nullable|array',
            'conversion_units.*.unit'           => 'required|string|max:20',
            'conversion_units.*.conversion_value' => 'required|integer|min:1',
            'conversion_units.*.price'          => 'required|numeric|min:0',
        ]);

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

        $product = Product::create($validated);

        // Save satuan konversi
        if ($request->filled('conversion_units')) {
            foreach ($request->conversion_units as $cu) {
                if (!empty($cu['unit']) && !empty($cu['conversion_value']) && isset($cu['price'])) {
                    $product->productUnits()->create([
                        'unit'             => $cu['unit'],
                        'conversion_value' => (int) $cu['conversion_value'],
                        'price'            => $cu['price'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(Product $product)
    {
        $product->load('productUnits');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'                              => 'required|string|max:255',
            'product_code'                      => 'nullable|string|max:50|unique:products,product_code,' . $product->id,
            'category_id'                       => 'required|exists:categories,id',
            'description'                       => 'nullable|string',
            'unit'                              => 'required|string|max:20',
            'price'                             => 'required|numeric|min:0',
            'discount_price'                    => 'nullable|numeric|min:0|lt:price',
            'weight'                            => 'nullable|numeric|min:0',
            'stock'                             => 'required|integer|min:0',
            'stock_alert'                       => 'required|integer|min:0',
            'image'                             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'                         => 'boolean',
            'conversion_units'                  => 'nullable|array',
            'conversion_units.*.unit'           => 'required|string|max:20',
            'conversion_units.*.conversion_value' => 'required|integer|min:1',
            'conversion_units.*.price'          => 'required|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['discount_price'] = $request->filled('discount_price') ? ($request->price - $request->discount_price) : null;
        $validated['weight'] = $validated['weight'] ?? 0;

        // Ensure unique slug (exclude current product)
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

        $product->update($validated);

        // Sync satuan konversi
        $product->productUnits()->delete();
        if ($request->filled('conversion_units')) {
            foreach ($request->conversion_units as $cu) {
                if (!empty($cu['unit']) && !empty($cu['conversion_value']) && isset($cu['price'])) {
                    $product->productUnits()->create([
                        'unit'             => $cu['unit'],
                        'conversion_value' => (int) $cu['conversion_value'],
                        'price'            => $cu['price'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui!');
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

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $product->is_active,
            'message' => $product->is_active ? 'Produk diaktifkan' : 'Produk dinonaktifkan',
        ]);
    }
}
