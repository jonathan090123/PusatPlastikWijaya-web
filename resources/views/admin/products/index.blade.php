@extends('layouts.admin')

@section('title', 'Manajemen Produk - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-box"></i> Manajemen Produk</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Produk
    </a>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0.75rem 1.25rem;">
        <form action="{{ route('admin.products.index') }}" method="GET" style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px; margin:0;">
                <input type="text" name="search" placeholder="Cari produk..." value="{{ request('search') }}">
            </div>
            <div class="form-group" style="min-width:160px; margin:0;">
                <select name="category">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="min-width:130px; margin:0;">
                <select name="status">  
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
            @if(request()->hasAny(['search', 'category', 'status']))
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
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
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Berat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                    <tr>
                        <td>{{ $products->firstItem() + $index }}</td>
                        <td>
                            @if($product->image)
                                <div style="width:80px; height:80px; background:var(--gray-50); border-radius:var(--radius-sm); border:1px solid var(--gray-200); overflow:hidden; cursor:pointer;" onclick="openImgPreview(this.querySelector('img'))">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width:100%; height:100%; object-fit:contain; padding:4px;">
                                </div>
                            @else
                                <div style="width:80px; height:80px; background:var(--gray-100); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; color:var(--gray-400); font-size:1.5rem;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td>
                            <span class="badge-status badge-processing">{{ $product->category->name ?? '-' }}</span>
                        </td>
                        <td>
                            @if($product->hasDiscount())
                                <span style="text-decoration:line-through; color:var(--gray-400); font-size:0.8rem;">Rp {{ number_format($product->price, 0, ',', '.') }}</span><br>
                                <strong style="color:var(--danger);">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</strong>
                            @else
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            @endif
                        </td>
                        <td>
                            @if($product->isLowStock())
                                <span class="badge-status badge-cancelled" title="Stok menipis!"><i class="fas fa-exclamation-triangle"></i> {{ $product->stock }}</span>
                            @else
                                <span>{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($product->weight, 0) }}g</td>
                        <td>
                            <button class="btn btn-sm {{ $product->is_active ? 'btn-success' : 'btn-secondary' }} toggle-active"
                                    data-url="{{ route('admin.products.toggleActive', $product) }}">
                                <i class="fas {{ $product->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-icon btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>Belum ada produk</h3>
                                <p>Mulai tambahkan produk ke katalog</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer">
            {{ $products->links() }}
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
</script>
@endpush
