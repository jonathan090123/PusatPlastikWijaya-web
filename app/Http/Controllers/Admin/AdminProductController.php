<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AdminProductController extends Controller
{
    private function pushFileHistory(string $type, string $filename, ?int $count = null, array $payload = []): void
    {
        $history = session('product_file_history', []);
        $history[] = [
            'id' => uniqid('filehist_', true),
            'type' => $type,
            'filename' => $filename,
            'count' => $count,
            'created_at' => now()->toDateTimeString(),
            ...$payload,
        ];

        $history = array_slice($history, -5);
        session()->put('product_file_history', $history);
    }

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
        $fileHistory = session('product_file_history', []);

        return view('admin.products.index', compact('products', 'categories', 'fileHistory'));
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

    public function exportPriceTemplate(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $term = trim($request->search);
            $keywords = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);

            if (count($keywords) > 1) {
                $query->where(function ($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->where(function ($inner) use ($keyword) {
                            $inner->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('product_code', 'like', '%' . $keyword . '%');
                        });
                    }
                });
            } else {
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

        $products = $query->with('category', 'productUnits')->select(['id', 'category_id', 'product_code', 'name', 'unit', 'price', 'discount_price', 'weight', 'stock'])->get();

        // Build filename: export-product-(kategori)-(tanggal-jam).csv
        $filename = 'export-product';
        if ($request->filled('category')) {
            $category = Category::find($request->category);
            if ($category) {
                $filename .= '-' . strtolower(str_replace(' ', '-', $category->name));
            }
        }
        $filename .= '-' . now()->format('Ymd-His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        $maxUnits = $products->max(fn ($p) => $p->productUnits->count());

        $emptyCols = array_fill(0, 9 + $maxUnits * 3, '');
        fputcsv($handle, array_merge(
            ['Export data produk - isi kolom yang ingin diubah, bagian kosong akan dipertahankan.'],
            array_slice($emptyCols, 1)
        ));

        $headers = [
            'product_id',
            'category_name',
            'product_code',
            'name',
            'unit',
            'price',
            'discount_price',
            'weight',
            'stock',
        ];
        for ($i = 1; $i <= $maxUnits; $i++) {
            $headers[] = "product_unit_{$i}_unit";
            $headers[] = "product_unit_{$i}_conversion";
            $headers[] = "product_unit_{$i}_price";
        }
        fputcsv($handle, $headers);

        foreach ($products as $product) {
            $row = [
                $product->id,
                $product->category->name ?? '',
                $product->product_code ?? '',
                $product->name,
                $product->unit,
                (float) $product->price,
                $product->discount_price ?? '',
                $product->weight ?? '',
                $product->stock,
            ];
            $units = $product->productUnits->values();
            for ($i = 0; $i < $maxUnits; $i++) {
                if (isset($units[$i])) {
                    $row[] = $units[$i]->unit;
                    $row[] = $units[$i]->conversion_value;
                    $row[] = (float) $units[$i]->price;
                } else {
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                }
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $this->pushFileHistory('export', $filename, $products->count());

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportSelected(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Tidak ada produk yang dipilih untuk diexport.');
        }

        $products = Product::with(['category', 'productUnits'])
            ->select(['id', 'category_id', 'product_code', 'name', 'unit', 'price', 'discount_price', 'weight', 'stock'])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        // Build filename: export-product-(kategori)-(tanggal-jam).csv
        $filename = 'export-product';
        $uniqueCategories = $products->pluck('category_id')->unique();
        if ($uniqueCategories->count() === 1) {
            $category = $products->first()->category;
            if ($category) {
                $filename .= '-' . strtolower(str_replace(' ', '-', $category->name));
            }
        }
        $filename .= '-' . now()->format('Ymd-His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        $maxUnits = $products->max(fn ($p) => $p->productUnits->count());

        $emptyCols = array_fill(0, 9 + $maxUnits * 3, '');
        fputcsv($handle, array_merge(
            ['Export produk terpilih - isi kolom yang ingin diubah, bagian kosong akan dipertahankan.'],
            array_slice($emptyCols, 1)
        ));

        $headers = [
            'product_id',
            'category_name',
            'product_code',
            'name',
            'unit',
            'price',
            'discount_price',
            'weight',
            'stock',
        ];
        for ($i = 1; $i <= $maxUnits; $i++) {
            $headers[] = "product_unit_{$i}_unit";
            $headers[] = "product_unit_{$i}_conversion";
            $headers[] = "product_unit_{$i}_price";
        }
        fputcsv($handle, $headers);

        foreach ($products as $product) {
            $row = [
                $product->id,
                $product->category->name ?? '',
                $product->product_code ?? '',
                $product->name,
                $product->unit,
                (float) $product->price,
                $product->discount_price ?? '',
                $product->weight ?? '',
                $product->stock,
            ];
            $units = $product->productUnits->values();
            for ($i = 0; $i < $maxUnits; $i++) {
                if (isset($units[$i])) {
                    $row[] = $units[$i]->unit;
                    $row[] = $units[$i]->conversion_value;
                    $row[] = (float) $units[$i]->price;
                } else {
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                }
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $this->pushFileHistory('export', $filename, $products->count());

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function importPriceUpdates(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $beforeSnapshot = [];
        $historyUpdates = [];

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return redirect()->route('admin.products.index')
                ->with('error', 'File tidak dapat dibaca.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return redirect()->route('admin.products.index')
                ->with('error', 'File kosong atau tidak valid.');
        }

        $normalizedHeader = array_map(fn ($value) => trim((string) $value), $header);
        if (!in_array('product_id', $normalizedHeader, true)) {
            $header = fgetcsv($handle);
            if ($header === false) {
                fclose($handle);
                return redirect()->route('admin.products.index')
                    ->with('error', 'File kosong atau tidak valid.');
            }
            $normalizedHeader = array_map(fn ($value) => trim((string) $value), $header);
        }

        // Detect product_unit columns in header
        $productUnitCols = [];
        foreach ($normalizedHeader as $col) {
            if (preg_match('/^product_unit_(\d+)_(unit|conversion|price)$/', $col, $m)) {
                $productUnitCols[(int) $m[1]][$m[2]] = $col;
            }
        }
        ksort($productUnitCols);

        $updatedCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if (empty(array_filter($row, fn ($value) => $value !== null && $value !== ''))) {
                    continue;
                }

                $data = array_combine($normalizedHeader, $row);
                if (!is_array($data)) {
                    continue;
                }

                $productId = trim((string) ($data['product_id'] ?? ''));
                if ($productId === '') {
                    $errors[] = 'Baris ' . $rowNumber . ': product_id kosong.';
                    continue;
                }

                $product = Product::find($productId);
                if (!$product) {
                    $errors[] = 'Baris ' . $rowNumber . ': produk tidak ditemukan.';
                    continue;
                }

                $updates = [];

                if (isset($data['name']) && trim((string) $data['name']) !== '') {
                    $updates['name'] = trim((string) $data['name']);
                }

                if (isset($data['product_code']) && trim((string) $data['product_code']) !== '') {
                    $updates['product_code'] = trim((string) $data['product_code']);
                }

                if (isset($data['unit']) && trim((string) $data['unit']) !== '') {
                    $updates['unit'] = trim((string) $data['unit']);
                }

                if (isset($data['price']) && trim((string) $data['price']) !== '') {
                    $newPrice = (float) preg_replace('/[^0-9.-]/', '', (string) $data['price']);
                    if ($newPrice < 0) {
                        $errors[] = 'Baris ' . $rowNumber . ': harga tidak boleh negatif.';
                        continue;
                    }
                    $updates['price'] = $newPrice;
                }

                if (array_key_exists('discount_price', $data)) {
                    $newDiscountPriceValue = trim((string) $data['discount_price']);
                    if ($newDiscountPriceValue === '') {
                        $updates['discount_price'] = null;
                    } else {
                        $newDiscountPrice = (float) preg_replace('/[^0-9.-]/', '', $newDiscountPriceValue);
                        if ($newDiscountPrice < 0) {
                            $errors[] = 'Baris ' . $rowNumber . ': harga diskon tidak boleh negatif.';
                            continue;
                        }
                        $updates['discount_price'] = $newDiscountPrice;
                    }
                }

                if (isset($data['weight']) && trim((string) $data['weight']) !== '') {
                    $updates['weight'] = (float) preg_replace('/[^0-9.-]/', '', (string) $data['weight']);
                }

                if (isset($data['stock']) && trim((string) $data['stock']) !== '') {
                    $updates['stock'] = (int) $data['stock'];
                }

                // Process product_unit columns
                $productUnitUpdates = [];
                if (!empty($productUnitCols)) {
                    foreach ($productUnitCols as $idx => $cols) {
                        $unitName = trim((string) ($data[$cols['unit']] ?? ''));
                        $conversion = trim((string) ($data[$cols['conversion']] ?? ''));
                        $unitPrice = trim((string) ($data[$cols['price']] ?? ''));
                        if ($unitName !== '' && $conversion !== '' && $unitPrice !== '') {
                            $productUnitUpdates[] = [
                                'unit' => $unitName,
                                'conversion_value' => (int) $conversion,
                                'price' => (float) preg_replace('/[^0-9.-]/', '', $unitPrice),
                            ];
                        }
                    }
                }

                $hasChanges = !empty($updates) || !empty($productUnitUpdates);

                if ($hasChanges) {
                    $beforeSnapshot[] = [
                        'id' => $product->id,
                        'price' => $product->price,
                        'discount_price' => $product->discount_price,
                        'product_units' => $product->productUnits->map(fn ($pu) => [
                            'unit' => $pu->unit,
                            'conversion_value' => $pu->conversion_value,
                            'price' => (float) $pu->price,
                        ])->toArray(),
                    ];

                    if (!empty($updates)) {
                        $product->update($updates);
                    }

                    foreach ($productUnitUpdates as $puData) {
                        $product->productUnits()->updateOrCreate(
                            ['unit' => $puData['unit']],
                            [
                                'conversion_value' => $puData['conversion_value'],
                                'price' => $puData['price'],
                            ]
                        );
                    }

                    $product->load('productUnits');

                    $historyUpdates[] = [
                        'product_id' => $product->id,
                        'new_price' => $updates['price'] ?? null,
                        'new_discount_price' => $updates['discount_price'] ?? null,
                        'new_product_units' => $product->productUnits->map(fn ($pu) => [
                            'unit' => $pu->unit,
                            'conversion_value' => $pu->conversion_value,
                            'price' => (float) $pu->price,
                        ])->toArray(),
                    ];
                    $updatedCount++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->route('admin.products.index')
                ->with('error', 'Gagal mengimpor data harga: ' . $e->getMessage());
        } finally {
            fclose($handle);
        }

        $message = 'Berhasil memperbarui ' . $updatedCount . ' produk.';
        if (!empty($errors)) {
            return redirect()->route('admin.products.index')
                ->with('success', $message)
                ->with('error', implode(' ', $errors));
        }

        if (!empty($beforeSnapshot)) {
            session()->put('price_import_backup', [
                'updated_at' => now()->toDateTimeString(),
                'products' => $beforeSnapshot,
            ]);
        }

        $this->pushFileHistory('import', $request->file('file')->getClientOriginalName(), $updatedCount, [
            'updates' => $historyUpdates,
            'before' => $beforeSnapshot,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', $message . ' Anda dapat mengurungkan perubahan ini jika perlu.');
    }

    public function applyFileHistory(Request $request)
    {
        $historyId = (string) $request->input('history_id');
        $history = session('product_file_history', []);
        $entry = collect($history)->firstWhere('id', $historyId);

        if (!$entry || empty($entry['updates'])) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Riwayat file tidak tersedia untuk diterapkan.');
        }

        DB::beginTransaction();

        try {
            foreach ($entry['updates'] as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $updates = [];
                    if ($item['new_price'] !== null) {
                        $updates['price'] = (float) $item['new_price'];
                    }
                    if (array_key_exists('new_discount_price', $item) && $item['new_discount_price'] !== null) {
                        $updates['discount_price'] = (float) $item['new_discount_price'];
                    }
                    if (!empty($updates)) {
                        $product->update($updates);
                    }

                    if (isset($item['new_product_units'])) {
                        $product->productUnits()->delete();
                        foreach ($item['new_product_units'] as $pu) {
                            $product->productUnits()->create($pu);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')
                ->with('success', 'Isi produk berhasil diterapkan dari file history.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.products.index')
                ->with('error', 'Gagal menerapkan file history: ' . $e->getMessage());
        }
    }

    public function undoFileHistory(Request $request)
    {
        $historyId = (string) $request->input('history_id');
        $history = session('product_file_history', []);
        $entry = collect($history)->firstWhere('id', $historyId);

        if (!$entry || empty($entry['before'])) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Tidak ada data sebelumnya untuk dibatalkan dari file ini.');
        }

        DB::beginTransaction();

        try {
            foreach ($entry['before'] as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    $product->update([
                        'price' => $item['price'],
                        'discount_price' => $item['discount_price'],
                    ]);

                    if (isset($item['product_units'])) {
                        $product->productUnits()->delete();
                        foreach ($item['product_units'] as $pu) {
                            $product->productUnits()->create($pu);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')
                ->with('success', 'Perubahan dari file history berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.products.index')
                ->with('error', 'Gagal membatalkan file history: ' . $e->getMessage());
        }
    }

    public function undoPriceImport()
    {
        $backup = session('price_import_backup');
        if (empty($backup['products'])) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Tidak ada riwayat impor harga yang bisa dikembalikan.');
        }

        DB::beginTransaction();

        try {
            foreach ($backup['products'] as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    $product->update([
                        'price' => $item['price'],
                        'discount_price' => $item['discount_price'],
                    ]);

                    if (isset($item['product_units'])) {
                        $product->productUnits()->delete();
                        foreach ($item['product_units'] as $pu) {
                            $product->productUnits()->create($pu);
                        }
                    }
                }
            }

            DB::commit();
            session()->forget('price_import_backup');

            return redirect()->route('admin.products.index')
                ->with('success', 'Perubahan impor harga berhasil dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.products.index')
                ->with('error', 'Gagal mengembalikan perubahan: ' . $e->getMessage());
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
