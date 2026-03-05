@extends('layouts.admin')

@section('title', 'Edit Kategori - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit"></i> Edit Kategori</h1>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label><i class="fas fa-tag"></i> Nama Kategori <span style="color:var(--danger);">*</span></label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Masukkan nama kategori" required>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Deskripsi</label>
                <textarea name="description" rows="3" placeholder="Deskripsi kategori (opsional)">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-image"></i> Gambar</label>
                @if($category->image)
                    <div style="margin-bottom:0.75rem;">
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" style="max-width:200px; border-radius:var(--radius); border:2px solid var(--gray-200);">
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
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    Aktifkan kategori ini
                </label>
            </div>

            <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
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
