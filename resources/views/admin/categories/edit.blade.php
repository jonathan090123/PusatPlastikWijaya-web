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
                    <div id="currentImageWrapper" style="margin-bottom:0.75rem;">
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" style="max-width:200px; border-radius:var(--radius); border:2px solid var(--gray-200);">
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
            @if($category->image)
            <div id="modalDeleteImage" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
                <div onclick="document.getElementById('modalDeleteImage').style.display='none'; document.body.style.overflow=''" style="position:absolute; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);"></div>
                <div style="position:relative; background:#fff; border-radius:var(--radius); padding:2rem 1.75rem 1.5rem; width:100%; max-width:420px; margin:1rem; box-shadow:0 20px 60px rgba(0,0,0,0.2); text-align:center;">
                    <div style="text-align:center; margin-bottom:1rem; font-size:2.25rem;">
                        <i class="fas fa-trash-alt" style="color:#ef4444;"></i>
                    </div>
                    <h3 style="margin:0 0 0.5rem; font-size:1.1rem; font-weight:700; color:var(--gray-800);">Hapus Gambar Kategori?</h3>
                    <p style="font-size:0.875rem; color:var(--gray-600); margin:0 0 1.5rem; line-height:1.55;">Gambar kategori akan dihapus permanen dan tidak dapat dikembalikan. Pastikan Anda yakin sebelum melanjutkan.</p>
                    <div style="display:flex; gap:0.75rem; justify-content:center;">
                        <button type="button" id="btnCancelDeleteImage"
                                style="flex:1; padding:0.6rem 1rem; border:1px solid var(--gray-200); border-radius:var(--radius-sm); background:#fff; color:var(--gray-700); font-size:0.875rem; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="button" id="btnConfirmDeleteImage"
                                data-url="{{ route('admin.categories.deleteImage', $category) }}"
                                style="flex:1; padding:0.6rem 1rem; border:none; border-radius:var(--radius-sm); background:#ef4444; color:#fff; font-size:0.875rem; font-weight:600; cursor:pointer;">
                            <i class="fas fa-trash"></i> Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
            @endif

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

// Delete Image — AJAX (no page reload)
const btnDeleteImage        = document.getElementById('btnDeleteImage');
const modalDeleteImage      = document.getElementById('modalDeleteImage');
const btnCancelDeleteImage  = document.getElementById('btnCancelDeleteImage');
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
        const url   = this.dataset.url;
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
                modalDeleteImage.style.display = 'none';
                document.body.style.overflow = '';

                const wrapper = document.getElementById('currentImageWrapper');
                if (wrapper) wrapper.remove();

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
</script>
@endpush
