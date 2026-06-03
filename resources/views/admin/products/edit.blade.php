@extends('layouts.admin')

@section('title', 'Edit Produk - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit"></i> Edit Produk</h1>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Nama Produk <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-barcode"></i> Kode Barang</label>
                    <input type="text" name="product_code" value="{{ old('product_code', $product->product_code) }}" class="{{ $errors->has('product_code') ? 'is-invalid' : '' }}" placeholder="cth: KRS-P15-DRG" style="font-family:monospace;">
                    @error('product_code')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tags"></i> Kategori <span style="color:var(--danger);">*</span></label>
                    <select name="category_id" class="{{ $errors->has('category_id') ? 'is-invalid' : '' }}" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Deskripsi</label>
                <textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:1rem;">
                <div class="form-group">
                    <label><i class="fas fa-balance-scale"></i> Satuan Dasar <span style="color:var(--danger);">*</span></label>
                    <select name="unit" id="baseUnit" class="{{ $errors->has('unit') ? 'is-invalid' : '' }}" required>
                        @foreach(['KG','PAK','ROL','PCS','BH','SAP','P100','BAL','IKT','DOS','PRS'] as $u)
                            <option value="{{ $u }}" {{ old('unit', $product->unit) == $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                    @error('unit')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-money-bill"></i> Harga (Rp) <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="price" value="{{ old('price', $product->price) }}" min="1" step="1" required>
                    @error('price')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Harga Diskon (Rp)</label>
                    <input type="number" name="discount_price" value="{{ old('discount_price', $product->discount_price ? ($product->price - $product->discount_price) : '') }}" placeholder="Potongan (cth: 1000)" min="1" step="1">
                    @error('discount_price')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-weight-hanging"></i> Berat (gram)</label>
                    <input type="number" name="weight" value="{{ old('weight', $product->weight) }}" min="0" step="1">
                    @error('weight')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-cubes"></i> Stok <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                    @error('stock')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-bell"></i> Alert Stok</label>
                    <input type="number" name="stock_alert" value="{{ old('stock_alert', $product->stock_alert) }}" min="0">
                    @error('stock_alert')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Satuan Konversi --}}
            <div class="form-group">
                <label><i class="fas fa-layer-group"></i> Satuan Konversi
                    <small style="font-weight:normal; color:var(--gray-400);">(opsional — mis. BAL, IKT, DOS)</small>
                </label>
                <div id="conversionUnitsContainer" style="display:flex; flex-direction:column; gap:0.5rem;">
                    @php
                        $cuUnits = ['KG','PAK','ROL','PCS','BH','SAP','P100','BAL','IKT','DOS','PRS'];
                        $existingUnits = old('conversion_units') ? collect(old('conversion_units')) : $product->productUnits;
                    @endphp
                    @foreach($existingUnits as $i => $cu)
                    @php
                        $cuUnit  = is_array($cu) ? ($cu['unit'] ?? '') : $cu->unit;
                        $cuConv  = is_array($cu) ? ($cu['conversion_value'] ?? '') : $cu->conversion_value;
                        $cuPrice = is_array($cu) ? ($cu['price'] ?? '') : $cu->price;
                    @endphp
                    <div class="cu-row" style="display:grid; grid-template-columns:110px 1fr 1fr auto; gap:0.5rem; align-items:center;">
                        <select name="conversion_units[{{ $i }}][unit]">
                            @foreach($cuUnits as $u)
                                <option value="{{ $u }}" {{ $cuUnit == $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="conversion_units[{{ $i }}][conversion_value]" value="{{ $cuConv }}" placeholder="1 satuan ini = ? satuan dasar" min="1">
                        <input type="number" name="conversion_units[{{ $i }}][price]" value="{{ $cuPrice }}" placeholder="Harga satuan ini (Rp)" min="1" step="1">
                        <button type="button" onclick="this.closest('.cu-row').remove()" class="btn btn-icon btn-danger" title="Hapus"><i class="fas fa-times"></i></button>
                    </div>
                    @endforeach
                </div>
                <button type="button" id="addConvUnit" class="btn btn-secondary btn-sm" style="margin-top:0.5rem;">
                    <i class="fas fa-plus"></i> Tambah Satuan Konversi
                </button>
                <small style="display:block; margin-top:0.375rem; color:var(--gray-400);">Contoh: BAL &bull; nilai konversi 25 (1 BAL = 25 KG) &bull; harga Rp 675.000</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-image"></i> Gambar Produk</label>
                @if($product->image)
                    <div id="currentImageWrapper" style="margin-bottom:0.75rem;">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-width:200px; border-radius:var(--radius); border:2px solid var(--gray-200);">
                        <p style="font-size:0.8rem; color:var(--gray-500); margin-top:0.25rem;">Gambar saat ini</p>
                        <button type="button" id="btnDeleteImage" class="btn btn-sm" style="margin-top:0.5rem; background:var(--danger,#ef4444); color:#fff; border:none; border-radius:var(--radius,6px); padding:0.35rem 0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.4rem; font-size:0.82rem;">
                            <i class="fas fa-trash"></i> Hapus Gambar
                        </button>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" id="imageInput">
                <div id="imagePreview" style="margin-top:0.75rem; display:none;">
                    <img src="" alt="Preview" style="max-width:200px; border-radius:var(--radius); border:2px solid var(--gray-200);">
                    <p style="font-size:0.8rem; color:var(--gray-500); margin-top:0.25rem;">Gambar baru</p>
                </div>
                @error('image')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Modal Konfirmasi Hapus Gambar --}}
            @if($product->image)
            <div id="modalDeleteImage" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
                <div onclick="document.getElementById('modalDeleteImage').style.display='none'" style="position:absolute; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);"></div>
                <div style="position:relative; background:#fff; border-radius:var(--radius); padding:2rem 1.75rem 1.5rem; width:100%; max-width:420px; margin:1rem; box-shadow:0 20px 60px rgba(0,0,0,0.2); text-align:center;">
                    <div style="text-align:center; margin-bottom:1rem; font-size:2.25rem;">
                        <i class="fas fa-trash-alt" style="color:#ef4444;"></i>
                    </div>
                    <h3 style="margin:0 0 0.5rem; font-size:1.1rem; font-weight:700; color:var(--gray-800);">Hapus Gambar Produk?</h3>
                    <p style="font-size:0.875rem; color:var(--gray-600); margin:0 0 1.5rem; line-height:1.55;">Gambar produk akan dihapus permanen dan tidak dapat dikembalikan. Pastikan Anda yakin sebelum melanjutkan.</p>
                    <div style="display:flex; gap:0.75rem; justify-content:center;">
                        <button type="button" id="btnCancelDeleteImage"
                                style="flex:1; padding:0.6rem 1rem; border:1px solid var(--gray-200); border-radius:var(--radius-sm); background:#fff; color:var(--gray-700); font-size:0.875rem; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="button" id="btnConfirmDeleteImage"
                                data-url="{{ route('admin.products.deleteImage', $product) }}"
                                style="flex:1; padding:0.6rem 1rem; border:none; border-radius:var(--radius-sm); background:#ef4444; color:#fff; font-size:0.875rem; font-weight:600; cursor:pointer;">
                            <i class="fas fa-trash"></i> Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                    Aktifkan produk ini
                </label>
            </div>

            <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.display = 'block';
            preview.querySelector('img').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});

// Delete Image — AJAX (no page reload)
const btnDeleteImage       = document.getElementById('btnDeleteImage');
const modalDeleteImage     = document.getElementById('modalDeleteImage');
const btnCancelDeleteImage = document.getElementById('btnCancelDeleteImage');
const btnConfirmDeleteImage = document.getElementById('btnConfirmDeleteImage');

if (btnDeleteImage && modalDeleteImage) {
    btnDeleteImage.addEventListener('click', () => {
        modalDeleteImage.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    btnCancelDeleteImage.addEventListener('click', () => {
        modalDeleteImage.style.display = 'none';
        document.body.style.overflow = '';
    });

    btnConfirmDeleteImage.addEventListener('click', function () {
        const url = this.dataset.url;
        const token = document.querySelector('meta[name="csrf-token"]')?.content
                   || '{{ csrf_token() }}';

        btnConfirmDeleteImage.disabled = true;
        btnConfirmDeleteImage.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Tutup modal
                modalDeleteImage.style.display = 'none';
                document.body.style.overflow = '';

                // Hapus elemen gambar dari DOM
                const wrapper = document.getElementById('currentImageWrapper');
                if (wrapper) wrapper.remove();

                // Sembunyikan modal dari DOM agar tidak muncul lagi
                modalDeleteImage.remove();
            }
        })
        .catch(() => {
            btnConfirmDeleteImage.disabled = false;
            btnConfirmDeleteImage.innerHTML = '<i class="fas fa-trash"></i> Ya, Hapus';
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    });
}

// Satuan Konversi - dynamic rows
const cuUnits = ['KG','PAK','ROL','PCS','BH','SAP','P100','BAL','IKT','DOS','PRS'];
let cuIndex = document.querySelectorAll('#conversionUnitsContainer .cu-row').length;

document.getElementById('addConvUnit').addEventListener('click', function() {
    const container = document.getElementById('conversionUnitsContainer');
    const row = document.createElement('div');
    row.className = 'cu-row';
    row.style.cssText = 'display:grid; grid-template-columns:110px 1fr 1fr auto; gap:0.5rem; align-items:center;';
    row.innerHTML = `
        <select name="conversion_units[${cuIndex}][unit]">
            ${cuUnits.map(u => `<option value="${u}">${u}</option>`).join('')}
        </select>
        <input type="number" name="conversion_units[${cuIndex}][conversion_value]" placeholder="1 satuan ini = ? satuan dasar" min="1">
        <input type="number" name="conversion_units[${cuIndex}][price]" placeholder="Harga satuan ini (Rp)" min="1" step="1">
        <button type="button" onclick="this.closest('.cu-row').remove()" class="btn btn-icon btn-danger" title="Hapus"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(row);
    cuIndex++;
});
</script>
@endpush
