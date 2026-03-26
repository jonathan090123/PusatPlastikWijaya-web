@extends('layouts.admin')

@section('title', 'Manajemen Kategori - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-tags"></i> Manajemen Kategori</h1>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Kategori
    </a>
</div>

{{-- Search --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0.75rem 1.25rem;">
        <form action="{{ route('admin.categories.index') }}" method="GET" style="display:flex; gap:0.75rem; align-items:center;">
            <div class="form-group" style="flex:1; margin:0;">
                <input type="text" name="search" placeholder="Cari kategori..." value="{{ request('search') }}" style="width:100%;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
            @if(request('search'))
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Produk</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                    <tr>
                        <td>{{ $categories->firstItem() + $index }}</td>
                        <td>
                            @if($category->image)
                                <div style="width:80px; height:80px; background:var(--gray-50); border-radius:var(--radius-sm); border:1px solid var(--gray-200); overflow:hidden; cursor:pointer;" onclick="openImgPreview(this.querySelector('img'))">
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" style="width:100%; height:100%; object-fit:contain; padding:4px;">
                                </div>
                            @else
                                <div style="width:80px; height:80px; background:var(--gray-100); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; color:var(--gray-400); font-size:1.5rem;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td>{{ Str::limit($category->description, 50) ?? '-' }}</td>
                        <td>
                            <span class="badge-status badge-processing">{{ $category->products_count }} produk</span>
                        </td>
                        <td>
                            <button class="btn btn-sm {{ $category->is_active ? 'btn-success' : 'btn-secondary' }} toggle-active"
                                    data-id="{{ $category->id }}"
                                    data-url="{{ route('admin.categories.toggleActive', $category) }}">
                                <i class="fas {{ $category->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-icon btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-icon btn-danger delete-btn"
                                        data-name="{{ $category->name }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <h3>Belum ada kategori</h3>
                                <p>Mulai tambahkan kategori produk</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
        <div class="card-footer">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function openImgPreview(img) {
    if (img.classList.contains('img-zoomed')) return;
    img.classList.add('img-zoomed');
    setTimeout(function() {
        document.addEventListener('click', function closePreview() {
            img.classList.remove('img-zoomed');
            document.removeEventListener('click', closePreview);
        });
    }, 0);
}

document.querySelectorAll('.toggle-active').forEach(btn => {
    btn.addEventListener('click', function() {
        const url = this.dataset.url;
        const button = this;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                button.className = 'btn btn-sm btn-secondary toggle-active';
                    button.innerHTML = '<i class="fas fa-times"></i> Nonaktif';
                }
            }
        });
    });
});

// Delete confirm
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.delete-btn');
    if (!btn) return;
    var form = btn.closest('.delete-form');
    var name = btn.dataset.name || 'item ini';
    wwConfirm(
        'Hapus Kategori?',
        'Kategori "' + name + '" akan dihapus secara permanen.',
        function() { form.submit(); }
    );
});
</script>
@endpush
