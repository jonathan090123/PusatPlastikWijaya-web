@extends('layouts.admin')

@section('title', 'Tambah Produk - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-plus"></i> Tambah Produk</h1>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Nama Produk <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Masukkan nama produk" required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-barcode"></i> Kode Barang</label>
                    <input type="text" name="product_code" value="{{ old('product_code') }}" class="{{ $errors->has('product_code') ? 'is-invalid' : '' }}" placeholder="cth: KRS-P15-DRG" style="font-family:monospace;">
                    <small style="color:var(--gray-400); font-size:0.75rem;">Digunakan sebagai kata kunci pencarian</small>
                    @error('product_code')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tags"></i> Kategori <span style="color:var(--danger);">*</span></label>
                    <select name="category_id" class="{{ $errors->has('category_id') ? 'is-invalid' : '' }}" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Deskripsi</label>
                <textarea name="description" rows="4" placeholder="Deskripsi produk (opsional)">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:1rem;">
                <div class="form-group">
                    <label><i class="fas fa-balance-scale"></i> Satuan Dasar <span style="color:var(--danger);">*</span></label>
                    <select name="unit" id="baseUnit" class="{{ $errors->has('unit') ? 'is-invalid' : '' }}" required>
                        @foreach(['KG','PAK','ROL','PCS','BH','SAP','P100','BAL','IKT','DOS','PRS'] as $u)
                            <option value="{{ $u }}" {{ old('unit', 'PCS') == $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                    @error('unit')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-money-bill"></i> Harga (Rp) <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="price" value="{{ old('price') }}" class="{{ $errors->has('price') ? 'is-invalid' : '' }}" placeholder="0" min="0" step="100" required>
                    @error('price')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Harga Diskon (Rp)</label>
                    <input type="number" name="discount_price" value="{{ old('discount_price') }}" placeholder="Kosong = tanpa diskon" min="0" step="100">
                    @error('discount_price')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-weight-hanging"></i> Berat (gram)</label>
                    <input type="number" name="weight" value="{{ old('weight', 0) }}" class="{{ $errors->has('weight') ? 'is-invalid' : '' }}" placeholder="0" min="0" step="1">
                    @error('weight')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-cubes"></i> Stok <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}" class="{{ $errors->has('stock') ? 'is-invalid' : '' }}" placeholder="0" min="0" required>
                    @error('stock')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-bell"></i> Alert Stok</label>
                    <input type="number" name="stock_alert" value="{{ old('stock_alert', 5) }}" placeholder="5" min="0">
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
                    @php $cuUnits = ['KG','PAK','ROL','PCS','BH','SAP','P100','BAL','IKT','DOS','PRS']; @endphp
                    @if(old('conversion_units'))
                        @foreach(old('conversion_units') as $i => $cu)
                        <div class="cu-row" style="display:grid; grid-template-columns:110px 1fr 1fr auto; gap:0.5rem; align-items:center;">
                            <select name="conversion_units[{{ $i }}][unit]">
                                @foreach($cuUnits as $u)
                                    <option value="{{ $u }}" {{ ($cu['unit'] ?? '') == $u ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="conversion_units[{{ $i }}][conversion_value]" value="{{ $cu['conversion_value'] ?? '' }}" placeholder="1 satuan ini = ? satuan dasar" min="1">
                            <input type="number" name="conversion_units[{{ $i }}][price]" value="{{ $cu['price'] ?? '' }}" placeholder="Harga satuan ini (Rp)" min="0" step="100">
                            <button type="button" onclick="this.closest('.cu-row').remove()" class="btn btn-icon btn-danger" title="Hapus"><i class="fas fa-times"></i></button>
                        </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="addConvUnit" class="btn btn-secondary btn-sm" style="margin-top:0.5rem;">
                    <i class="fas fa-plus"></i> Tambah Satuan Konversi
                </button>
                <small style="display:block; margin-top:0.375rem; color:var(--gray-400);">Contoh: BAL &bull; nilai konversi 25 (1 BAL = 25 KG) &bull; harga Rp 675.000</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-image"></i> Gambar Produk</label>
                <input type="file" name="image" accept="image/*" id="imageInput">
                <div id="imagePreview" style="margin-top:0.75rem; display:none;">
                    <img src="" alt="Preview" style="max-width:200px; border-radius:var(--radius); border:2px solid var(--gray-200);">
                </div>
                @error('image')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    Aktifkan produk ini
                </label>
            </div>

            <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
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
        <input type="number" name="conversion_units[${cuIndex}][price]" placeholder="Harga satuan ini (Rp)" min="0" step="100">
        <button type="button" onclick="this.closest('.cu-row').remove()" class="btn btn-icon btn-danger" title="Hapus"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(row);
    cuIndex++;
});
</script>
@endpush
