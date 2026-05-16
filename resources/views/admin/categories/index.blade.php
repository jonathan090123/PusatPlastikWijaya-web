@extends('layouts.admin')

@section('title', 'Manajemen Kategori - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-tags"></i> Manajemen Kategori</h1>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Kategori
    </a>
</div>

{{-- Search & Filter --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0.75rem 1.25rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px; margin:0; position:relative;">
                <i class="fas fa-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--gray-400); pointer-events:none;"></i>
                <input type="text" id="categorySearch" placeholder="Cari kategori..."
                    value="{{ request('search') }}"
                    style="width:100%; padding-left:2rem;" autocomplete="off">
            </div>
            <div style="display:flex; gap:0.4rem;">
                <a href="{{ request()->fullUrlWithQuery(['status' => '', 'page' => null]) }}"
                   class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-secondary' }}">
                    Semua
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}"
                   class="btn btn-sm {{ request('status') === 'active' ? 'btn-success' : 'btn-secondary' }}">
                    <i class="fas fa-check"></i> Aktif
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}"
                   class="btn btn-sm {{ request('status') === 'inactive' ? 'btn-warning' : 'btn-secondary' }}">
                    <i class="fas fa-times"></i> Nonaktif
                </a>
            </div>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm" id="searchReset">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </div>
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
                    <tr style="cursor:pointer;" onclick="if(!window.getSelection().toString())window.location='{{ route('admin.products.index') }}?category={{ $category->id }}'">
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
                        <td onclick="event.stopPropagation()">
                            <button class="btn btn-sm {{ $category->is_active ? 'btn-success' : 'btn-secondary' }} toggle-active"
                                    data-id="{{ $category->id }}"
                                    data-url="{{ route('admin.categories.toggleActive', $category) }}">
                                <i class="fas {{ $category->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;" onclick="event.stopPropagation()">
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

@push('styles')
<style>
.card .table-responsive table tbody tr[style*="cursor:pointer"]:hover {
    background: #eff6ff;
    box-shadow: inset 3px 0 0 var(--primary);
    transition: background 0.15s ease, box-shadow 0.15s ease;
}
</style>
@endpush

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
                if (data.is_active) {
                    button.className = 'btn btn-sm btn-success toggle-active';
                    button.innerHTML = '<i class="fas fa-check"></i> Aktif';
                } else {
                    button.className = 'btn btn-sm btn-secondary toggle-active';
                    button.innerHTML = '<i class="fas fa-times"></i> Nonaktif';
                }
            }
        });
    });
});

// Delete confirm — attach directly ke tiap tombol (bukan event delegation ke document)
// supaya tidak terblokir oleh event.stopPropagation() di wrapper div
document.querySelectorAll('.delete-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var form = btn.closest('.delete-form');
        var name = btn.dataset.name || 'item ini';
        wwConfirm(
            'Hapus Kategori?',
            'Kategori "' + name + '" akan dihapus permanen beserta semua produk di dalamnya.',
            function() { form.submit(); }
        );
    });
});

// Live search dengan debounce 400ms
(function() {
    var searchInput = document.getElementById('categorySearch');
    if (!searchInput) return;
    var timer;
    searchInput.addEventListener('input', function() {
        clearTimeout(timer);
        var val = this.value;
        timer = setTimeout(function() {
            var url = new URL(window.location.href);
            if (val.trim()) {
                url.searchParams.set('search', val.trim());
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 400);
    });
    // Fokus ke akhir input supaya cursor berada di ujung teks saat kembali dari redirect
    var len = searchInput.value.length;
    searchInput.setSelectionRange(len, len);
    searchInput.focus();
})();
</script>
@endpush
