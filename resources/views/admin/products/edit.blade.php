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

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Nama Produk <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                    @error('name')
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

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label><i class="fas fa-money-bill"></i> Harga (Rp) <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="100" required>
                    @error('price')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Harga Diskon (Rp)</label>
                    <input type="number" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" placeholder="Kosong = tanpa diskon" min="0" step="100">
                    @error('discount_price')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-weight-hanging"></i> Berat (gram) <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="weight" value="{{ old('weight', $product->weight) }}" min="0" step="1" required>
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

            <div class="form-group">
                <label><i class="fas fa-image"></i> Gambar Produk</label>
                @if($product->image)
                    <div style="margin-bottom:0.75rem;">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-width:200px; border-radius:var(--radius); border:2px solid var(--gray-200);">
                        <p style="font-size:0.8rem; color:var(--gray-500); margin-top:0.25rem;">Gambar saat ini</p>
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
</script>
@endpush
