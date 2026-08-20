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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

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

    // (import) Kolom export ringkas (revisi): data dasar + satuan konversi + harga per satuan + harga diskon
    private function officeHeaders(): array
    {
        return [
            'No.',
            'Item Category',
            'Item Name',
            'Unit',
            'Unit #2',
            'Unit 2 Ratio',
            'Unit #3',
            'Unit 3 Ratio',
            'Unit #4',
            'Unit 4 Ratio',
            'Unit #5',
            'Unit 5 Ratio',
            'Default Sales Price #1',
            'Default Sales Price #2',
            'Default Sales Price #3',
            'Default Sales Price #4',
            'Default Sales Price #5',
            'Discount Price',
        ];
    }

    // (export) Format angka 6 desimal persis item-list kantor (mis. 12 -> "12.000000")
    private function officeNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        return number_format((float) $value, 6, '.', '');
    }

    // (import) Satu baris data produk dalam urutan kolom export ringkas
    private function officeExportRow(int $no, Product $product): array
    {
        $units = $product->productUnits->values();
        $u2 = $units[0] ?? null;
        $u3 = $units[1] ?? null;
        $u4 = $units[2] ?? null;
        $u5 = $units[3] ?? null;

        return [
            $no,                                       // A  No.
            $product->category->name ?? '',            // B  Item Category
            $product->name,                            // C  Item Name
            $product->unit,                            // D  Unit
            $u2?->unit ?? '',                          // E  Unit #2
            $this->officeNumber($u2?->conversion_value),   // F  Unit 2 Ratio
            $u3?->unit ?? '',                          // G  Unit #3
            $this->officeNumber($u3?->conversion_value),   // H  Unit 3 Ratio
            $u4?->unit ?? '',                          // I  Unit #4
            $this->officeNumber($u4?->conversion_value),   // J  Unit 4 Ratio
            $u5?->unit ?? '',                          // K  Unit #5
            $this->officeNumber($u5?->conversion_value),   // L  Unit 5 Ratio
            $this->officeNumber($product->price),      // M  Default Sales Price #1
            $this->officeNumber($u2?->price),          // N  Default Sales Price #2
            $this->officeNumber($u3?->price),          // O  Default Sales Price #3
            $this->officeNumber($u4?->price),          // P  Default Sales Price #4
            $this->officeNumber($u5?->price),          // Q  Default Sales Price #5
            $this->officeNumber($product->discount_price), // R  Discount Price
        ];
    }

    // (import) Bangun file XLSX format kantor untuk diunduh
    private function officeExportResponse($products, string $filename)
    {
        $headers = $this->officeHeaders();

        return response()->streamDownload(function () use ($products, $headers) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Item');

            $sheet->fromArray($headers, null, 'A1');
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

            $no = 0;
            foreach ($products as $product) {
                $no++;
                $row = $this->officeExportRow($no, $product);
                $r = $no + 1;
                foreach ($row as $colIdx => $value) {
                    $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($colIdx + 1) . $r);
                    if ($colIdx === 0) {
                        $cell->setValue($value);
                    } else {
                        $cell->setValueExplicit($value, DataType::TYPE_STRING);
                    }
                }
            }

            $widths = array_fill(0, count($headers), 18);
            $widths[0] = 6;    // No.
            $widths[1] = 22;   // Item Category
            $widths[2] = 40;   // Item Name
            $widths[3] = 12;   // Unit
            $widths[4] = 14;   // Unit #2
            $widths[5] = 16;   // Unit 2 Ratio
            $widths[6] = 14;   // Unit #3
            $widths[7] = 16;   // Unit 3 Ratio
            $widths[8] = 14;   // Unit #4
            $widths[9] = 16;   // Unit 4 Ratio
            $widths[10] = 14;  // Unit #5
            $widths[11] = 16;  // Unit 5 Ratio
            $widths[12] = 22;  // Default Sales Price #1
            $widths[13] = 22;  // Default Sales Price #2
            $widths[14] = 22;  // Default Sales Price #3
            $widths[15] = 22;  // Default Sales Price #4
            $widths[16] = 22;  // Default Sales Price #5
            $widths[17] = 18;  // Discount Price
            foreach ($widths as $i => $w) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->getColumnDimension($col)->setWidth($w);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // (import) Ambil nilai sel/kolom CSV sebagai string bersih
    private function csvValue(array $row, array $colMap, string $key): string
    {
        $idx = $colMap[$key] ?? null;
        if ($idx === null || !array_key_exists($idx, $row)) {
            return '';
        }
        $v = $row[$idx];
        if ($v === null) {
            return '';
        }
        if ($v instanceof RichText) {
            $v = $v->getPlainText();
        }
        return trim((string) $v);
    }

    // (import) Ubah string angka (mendukung pemisah ribuan/desimal Indonesia) ke float
    private function parseNumeric($value): ?float
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }
        if (is_numeric($s)) {
            return (float) $s;
        }
        if (str_contains($s, ',')) {
            if (preg_match('/^\d{1,3}(\.\d{3})*,\d+$/', $s)) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                $s = str_replace(',', '', $s);
            }
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+(\.\d+)?$/', $s)) {
            $s = str_replace('.', '', $s);
        }
        return is_numeric($s) ? (float) $s : null;
    }

    // (import) Slug unik untuk nama produk
    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $base = $slug;
        $counter = 1;
        $query = Product::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        while ($query->exists()) {
            $slug = $base . '-' . $counter++;
            $query = Product::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }
        return $slug;
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

        $products = $query->with('category', 'productUnits')->get();

        // Build filename: export-product-(kategori)-(tanggal-jam).xlsx
        $filename = 'export-product';
        if ($request->filled('category')) {
            $category = Category::find($request->category);
            if ($category) {
                $filename .= '-' . strtolower(str_replace(' ', '-', $category->name));
            }
        }
        $filename .= '-' . now()->format('Ymd-His') . '.xlsx';

        $this->pushFileHistory('export', $filename, $products->count());

        return $this->officeExportResponse($products, $filename);
    }

    public function exportSelected(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Tidak ada produk yang dipilih untuk diexport.');
        }

        $products = Product::with(['category', 'productUnits'])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        // Build filename: export-product-(kategori)-(tanggal-jam).xlsx
        $filename = 'export-product';
        $uniqueCategories = $products->pluck('category_id')->unique();
        if ($uniqueCategories->count() === 1) {
            $category = $products->first()->category;
            if ($category) {
                $filename .= '-' . strtolower(str_replace(' ', '-', $category->name));
            }
        }
        $filename .= '-' . now()->format('Ymd-His') . '.xlsx';

        $this->pushFileHistory('export', $filename, $products->count());

        return $this->officeExportResponse($products, $filename);
    }

    public function importPriceUpdates(Request $request)
    {
        $request->validate([
            'file' => 'required|file|extensions:xlsx,xls,csv,txt',
        ]);

        $path = $request->file('file')->getRealPath();
        $extension = strtolower($request->file('file')->getClientOriginalExtension());

        $header = null;
        $rows = [];

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            try {
                $spreadsheet = IOFactory::load($path);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                for ($r = 1; $r <= $highestRow; $r++) {
                    $rows[] = $sheet->rangeToArray('A' . $r . ':' . $highestColumn . $r, null, true, false)[0];
                }
                $spreadsheet->disconnectWorksheets();
            } catch (\Exception $e) {
                return redirect()->route('admin.products.index')
                    ->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
            }

            // Ambil baris header pertama yang tidak kosong
            foreach ($rows as $i => $row) {
                if (array_filter(array_map(fn ($v) => trim((string) $v), $row))) {
                    $header = $row;
                    $rows = array_slice($rows, $i + 1);
                    break;
                }
            }
        } else {
            $handle = fopen($path, 'r');
            if ($handle === false) {
                return redirect()->route('admin.products.index')
                    ->with('error', 'File tidak dapat dibaca.');
            }
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        if (!$header) {
            return redirect()->route('admin.products.index')
                ->with('error', 'File kosong atau tidak valid.');
        }

        $normalizedHeader = array_map(fn ($value) => trim((string) $value), $header);
        $isOffice = in_array('Item Code', $normalizedHeader, true) || in_array('Item Name', $normalizedHeader, true);

        // Format lama web: baris pertama bisa berisi info, lewati jika bukan header
        if (!in_array('product_id', $normalizedHeader, true) && !$isOffice && count($rows) > 0) {
            $normalizedHeader = array_map(fn ($value) => trim((string) $value), array_shift($rows));
        }

        if (in_array('product_id', $normalizedHeader, true)) {
            return $this->importLegacyCsv($rows, $normalizedHeader, $request);
        }

        if ($isOffice) {
            return $this->importOfficeFormat($rows, $normalizedHeader, $request);
        }

        return redirect()->route('admin.products.index')
            ->with('error', 'Format file tidak dikenali. Gunakan hasil Export Produk atau file item-list kantor (kolom Item Code / Item Name).');
    }

    // (import) Snapshot produk sebelum diubah (untuk fitur undo)
    private function productSnapshot(Product $product): array
    {
        return [
            'id' => $product->id,
            'product_code' => $product->product_code,
            'name' => $product->name,
            'category_id' => $product->category_id,
            'unit' => $product->unit,
            'description' => $product->description,
            'price' => $product->price,
            'discount_price' => $product->discount_price,
            'weight' => $product->weight,
            'stock' => $product->stock,
            'stock_alert' => $product->stock_alert,
            'is_active' => $product->is_active,
            'slug' => $product->slug,
            'product_units' => $product->productUnits->map(fn ($pu) => [
                'unit' => $pu->unit,
                'conversion_value' => (float) $pu->conversion_value,
                'price' => (float) $pu->price,
            ])->toArray(),
        ];
    }

    // (import) Ringkasan update untuk riwayat file
    private function productHistoryUpdate(Product $product): array
    {
        return [
            'product_id' => $product->id,
            'new_price' => $product->price,
            'new_discount_price' => $product->discount_price,
            'new_product_units' => $product->productUnits->map(fn ($pu) => [
                'unit' => $pu->unit,
                'conversion_value' => (float) $pu->conversion_value,
                'price' => (float) $pu->price,
            ])->toArray(),
        ];
    }

    // (import) Akhiri import: simpan riwayat + backup undo
    private function finishImport(Request $request, int $updatedCount, int $createdCount, array $beforeSnapshot, array $historyUpdates, array $createdIds, array $errors)
    {
        $message = "Berhasil mengimpor produk: {$updatedCount} diperbarui, {$createdCount} produk baru.";

        if (!empty($errors)) {
            return redirect()->route('admin.products.index')
                ->with('success', $message)
                ->with('error', implode(' ', array_slice($errors, 0, 20)));
        }

        if (!empty($beforeSnapshot) || !empty($createdIds)) {
            session()->put('price_import_backup', [
                'updated_at' => now()->toDateTimeString(),
                'products' => $beforeSnapshot,
                'created' => $createdIds,
            ]);
        }

        $this->pushFileHistory('import', $request->file('file')->getClientOriginalName(), $updatedCount + $createdCount, [
            'updates' => $historyUpdates,
            'before' => $beforeSnapshot,
            'created' => $createdIds,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', $message . ' Anda dapat mengurungkan perubahan ini jika perlu.');
    }

    // (import) Import CSV lama web (kolom product_id)
    private function importLegacyCsv(array $rows, array $header, Request $request)
    {
        $beforeSnapshot = [];
        $historyUpdates = [];

        $productUnitCols = [];
        foreach ($header as $col) {
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
            foreach ($rows as $row) {
                $rowNumber++;
                if (empty(array_filter($row, fn ($value) => $value !== null && $value !== ''))) {
                    continue;
                }

                $data = [];
                foreach ($header as $i => $colName) {
                    $data[$colName] = $row[$i] ?? '';
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
                $get = fn ($key) => trim((string) ($data[$key] ?? ''));

                if ($get('name') !== '') {
                    $updates['name'] = $get('name');
                    $updates['slug'] = $this->makeUniqueSlug($get('name'), $product->id);
                }

                if ($get('product_code') !== '') {
                    $conflict = Product::where('product_code', $get('product_code'))->where('id', '!=', $product->id)->exists();
                    if ($conflict) {
                        $errors[] = 'Baris ' . $rowNumber . ': kode produk sudah dipakai produk lain.';
                    } else {
                        $updates['product_code'] = $get('product_code');
                    }
                }

                if ($get('unit') !== '') {
                    $updates['unit'] = $get('unit');
                }

                if ($get('price') !== '') {
                    $newPrice = $this->parseNumeric($get('price'));
                    if ($newPrice === null || $newPrice < 0) {
                        $errors[] = 'Baris ' . $rowNumber . ': harga tidak valid.';
                        continue;
                    }
                    $updates['price'] = $newPrice;
                }

                if (array_key_exists('discount_price', $data)) {
                    $val = trim((string) $data['discount_price']);
                    if ($val === '') {
                        $updates['discount_price'] = null;
                    } else {
                        $newDiscount = $this->parseNumeric($val);
                        if ($newDiscount === null || $newDiscount < 0) {
                            $errors[] = 'Baris ' . $rowNumber . ': harga diskon tidak valid.';
                            continue;
                        }
                        $updates['discount_price'] = $newDiscount;
                    }
                }

                if ($get('weight') !== '') {
                    $w = $this->parseNumeric($get('weight'));
                    if ($w !== null) {
                        $updates['weight'] = $w;
                    }
                }

                if ($get('stock') !== '') {
                    $updates['stock'] = (int) $get('stock');
                }

                $productUnitUpdates = [];
                if (!empty($productUnitCols)) {
                    foreach ($productUnitCols as $idx => $cols) {
                        $unitName = $get($cols['unit']);
                        $conversion = $get($cols['conversion']);
                        $unitPrice = $get($cols['price']);
                        if ($unitName !== '' && $conversion !== '' && $unitPrice !== '') {
                            $productUnitUpdates[] = [
                                'unit' => $unitName,
                                'conversion_value' => $this->parseNumeric($conversion),
                                'price' => $this->parseNumeric($unitPrice),
                            ];
                        }
                    }
                }

                $hasChanges = !empty($updates) || !empty($productUnitUpdates);

                if ($hasChanges) {
                    $beforeSnapshot[] = $this->productSnapshot($product);

                    if (!empty($updates)) {
                        $product->update($updates);
                    }

                    foreach ($productUnitUpdates as $puData) {
                        if ($puData['conversion_value'] !== null && $puData['price'] !== null) {
                            $product->productUnits()->updateOrCreate(
                                ['unit' => $puData['unit']],
                                [
                                    'conversion_value' => $puData['conversion_value'],
                                    'price' => $puData['price'],
                                ]
                            );
                        }
                    }

                    $product->load('productUnits');
                    $historyUpdates[] = $this->productHistoryUpdate($product);
                    $updatedCount++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.products.index')
                ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }

        return $this->finishImport($request, $updatedCount, 0, $beforeSnapshot, $historyUpdates, [], $errors);
    }

    // (import) Import format item-list kantor (kolom Item Code / Item Name / Default Sales Price #1/#2/#3)
    private function importOfficeFormat(array $rows, array $header, Request $request)
    {
        $colMap = array_flip($header);

        $unitSlots = [
            1 => ['unit' => 'Unit #2', 'ratio' => 'Unit 2 Ratio', 'price' => 'Default Sales Price #2'],
            2 => ['unit' => 'Unit #3', 'ratio' => 'Unit 3 Ratio', 'price' => 'Default Sales Price #3'],
            3 => ['unit' => 'Unit #4', 'ratio' => 'Unit 4 Ratio', 'price' => 'Default Sales Price #4'],
            4 => ['unit' => 'Unit #5', 'ratio' => 'Unit 5 Ratio', 'price' => 'Default Sales Price #5'],
        ];

        $updatedCount = 0;
        $createdCount = 0;
        $errors = [];
        $beforeSnapshot = [];
        $historyUpdates = [];
        $createdIds = [];

        try {
            DB::beginTransaction();

            $rowNumber = 1;
            foreach ($rows as $row) {
                $rowNumber++;

                $code = $this->csvValue($row, $colMap, 'Item Code');
                $name = $this->csvValue($row, $colMap, 'Item Name');
                if ($code === '' && $name === '') {
                    continue;
                }

                $category = null;
                $catName = $this->csvValue($row, $colMap, 'Item Category');
                if ($catName !== '') {
                    $category = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($catName)])->first();
                    if (!$category) {
                        $category = Category::create([
                            'name' => $catName,
                            'description' => null,
                            'is_active' => true,
                        ]);
                    }
                }

                $product = null;
                if ($code !== '') {
                    $product = Product::where('product_code', $code)->first();
                }
                if (!$product && $name !== '') {
                    $product = Product::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
                }

                $created = false;
                if (!$product) {
                    $categoryId = $category?->id;
                    if (!$categoryId) {
                        $fallback = Category::whereRaw('LOWER(name) = ?', ['umum'])->first();
                        if (!$fallback) {
                            $fallback = Category::create(['name' => 'Umum', 'description' => null, 'is_active' => true]);
                        }
                        $categoryId = $fallback->id;
                    }

                    $productName = $name !== '' ? $name : ($code !== '' ? $code : 'Produk Tanpa Nama');
                    $product = Product::create([
                        'category_id' => $categoryId,
                        'product_code' => $code !== '' ? $code : null,
                        'name' => $productName,
                        'slug' => $this->makeUniqueSlug($productName),
                        'unit' => $this->csvValue($row, $colMap, 'Unit') !== '' ? $this->csvValue($row, $colMap, 'Unit') : 'PCS',
                        'description' => $this->csvValue($row, $colMap, 'Notes') !== '' ? $this->csvValue($row, $colMap, 'Notes') : null,
                        'price' => $this->parseNumeric($this->csvValue($row, $colMap, 'Default Sales Price #1')) ?? 0,
                        'discount_price' => $this->parseNumeric($this->csvValue($row, $colMap, 'Discount Price')) ?? null,
                        'weight' => $this->parseNumeric($this->csvValue($row, $colMap, 'Weight (gr)')) ?? 0,
                        'stock' => 0,
                        'stock_alert' => (int) ($this->parseNumeric($this->csvValue($row, $colMap, 'Minimum Stock Reminder')) ?? 5),
                        'is_active' => strtoupper($this->csvValue($row, $colMap, 'Suspended')) !== 'YA',
                    ]);
                    $created = true;
                    $createdCount++;
                    $createdIds[] = $product->id;
                } else {
                    $beforeSnapshot[] = $this->productSnapshot($product);

                    $updates = [];
                    if ($code !== '') {
                        $conflict = Product::where('product_code', $code)->where('id', '!=', $product->id)->exists();
                        if ($conflict) {
                            $errors[] = "Baris $rowNumber: kode '$code' sudah dipakai produk lain, kode dilewati.";
                        } else {
                            $updates['product_code'] = $code;
                        }
                    }
                    if ($name !== '') {
                        $updates['name'] = $name;
                        $updates['slug'] = $this->makeUniqueSlug($name, $product->id);
                    }
                    if ($category) {
                        $updates['category_id'] = $category->id;
                    }
                    $unitVal = $this->csvValue($row, $colMap, 'Unit');
                    if ($unitVal !== '') {
                        $updates['unit'] = $unitVal;
                    }
                    $price = $this->parseNumeric($this->csvValue($row, $colMap, 'Default Sales Price #1'));
                    if ($price !== null && $price >= 0) {
                        $updates['price'] = $price;
                    }
                    $discount = $this->parseNumeric($this->csvValue($row, $colMap, 'Discount Price'));
                    if ($discount !== null && $discount >= 0) {
                        $updates['discount_price'] = $discount;
                    }
                    $weight = $this->parseNumeric($this->csvValue($row, $colMap, 'Weight (gr)'));
                    if ($weight !== null && $weight >= 0) {
                        $updates['weight'] = $weight;
                    }
                    $notes = $this->csvValue($row, $colMap, 'Notes');
                    if ($notes !== '') {
                        $updates['description'] = $notes;
                    }
                    $suspended = $this->csvValue($row, $colMap, 'Suspended');
                    if ($suspended !== '') {
                        $updates['is_active'] = strtoupper($suspended) !== 'YA';
                    }

                    if (!empty($updates)) {
                        $product->update($updates);
                    }
                    $updatedCount++;
                }

                // Rebuild satuan konversi dari kolom Unit #2..#5 + rasio + harga
                // (harga satuan lama dipertahankan jika kolom harga tidak ada di file)
                $existingUnits = $product->productUnits->keyBy(fn ($pu) => mb_strtolower($pu->unit));
                $unitsData = [];
                foreach ($unitSlots as $cols) {
                    $u = $this->csvValue($row, $colMap, $cols['unit']);
                    $ratio = $this->parseNumeric($this->csvValue($row, $colMap, $cols['ratio']));
                    $up = $this->parseNumeric($this->csvValue($row, $colMap, $cols['price']));
                    if ($u !== '' && $ratio !== null && $ratio > 0) {
                        if ($up === null || $up < 0) {
                            $existing = $existingUnits[mb_strtolower($u)] ?? null;
                            $up = $existing ? (float) $existing->price : 0;
                        }
                        $unitsData[] = [
                            'unit' => $u,
                            'conversion_value' => $ratio,
                            'price' => $up,
                        ];
                    }
                }

                $product->productUnits()->delete();
                foreach ($unitsData as $ud) {
                    $product->productUnits()->create($ud);
                }
                $product->load('productUnits');

                $historyUpdates[] = $this->productHistoryUpdate($product);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.products.index')
                ->with('error', 'Gagal mengimpor data item-list: ' . $e->getMessage());
        }

        return $this->finishImport($request, $updatedCount, $createdCount, $beforeSnapshot, $historyUpdates, $createdIds, $errors);
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

        if (!$entry || (empty($entry['before']) && empty($entry['created']))) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Tidak ada data sebelumnya untuk dibatalkan dari file ini.');
        }

        DB::beginTransaction();

        try {
            // Hapus produk yang baru dibuat dari file ini
            if (!empty($entry['created'])) {
                Product::whereIn('id', $entry['created'])->delete();
            }

            foreach ($entry['before'] as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    $product->update([
                        'product_code' => $item['product_code'],
                        'name' => $item['name'],
                        'slug' => $item['slug'],
                        'category_id' => $item['category_id'],
                        'unit' => $item['unit'],
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'discount_price' => $item['discount_price'],
                        'weight' => $item['weight'],
                        'stock' => $item['stock'],
                        'stock_alert' => $item['stock_alert'],
                        'is_active' => $item['is_active'],
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
        if (!$backup || (empty($backup['products']) && empty($backup['created']))) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Tidak ada riwayat impor yang bisa dikembalikan.');
        }

        DB::beginTransaction();

        try {
            // Hapus produk yang baru dibuat dari impor terakhir
            if (!empty($backup['created'])) {
                Product::whereIn('id', $backup['created'])->delete();
            }

            foreach ($backup['products'] as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    $product->update([
                        'product_code' => $item['product_code'],
                        'name' => $item['name'],
                        'slug' => $item['slug'],
                        'category_id' => $item['category_id'],
                        'unit' => $item['unit'],
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'discount_price' => $item['discount_price'],
                        'weight' => $item['weight'],
                        'stock' => $item['stock'],
                        'stock_alert' => $item['stock_alert'],
                        'is_active' => $item['is_active'],
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
                ->with('success', 'Perubahan impor berhasil dikembalikan.');
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
