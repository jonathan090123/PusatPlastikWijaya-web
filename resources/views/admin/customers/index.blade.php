@extends('layouts.admin')

@section('title', 'Manajemen Pelanggan - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-users"></i> Manajemen Pelanggan</h1>
</div>

{{-- Search & Filter --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0.75rem 1.25rem;">
        <form action="{{ route('admin.customers.index') }}" method="GET"
              style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <div class="form-group" style="flex:2; min-width:180px; margin:0;">
                <input type="text" name="search" placeholder="Cari nama, email, atau HP..." value="{{ request('search') }}">
            </div>
            <select name="filter" style="flex:1; min-width:150px; height:38px; border:1px solid var(--gray-200); border-radius:var(--radius-sm); padding:0 0.75rem; font-size:0.85rem; background:#fff; color:var(--gray-700);">
                <option value="">Semua Pelanggan</option>
                <option value="has_orders"  {{ request('filter')==='has_orders'  ? 'selected' : '' }}>Pernah Pesan</option>
                <option value="no_orders"   {{ request('filter')==='no_orders'   ? 'selected' : '' }}>Belum Pernah Pesan</option>
                <option value="has_points"  {{ request('filter')==='has_points'  ? 'selected' : '' }}>Punya Poin</option>
            </select>
            <select name="sort" style="flex:1; min-width:160px; height:38px; border:1px solid var(--gray-200); border-radius:var(--radius-sm); padding:0 0.75rem; font-size:0.85rem; background:#fff; color:var(--gray-700);">
                <option value="latest"      {{ request('sort','latest')==='latest'      ? 'selected' : '' }}>Terbaru</option>
                <option value="oldest"      {{ request('sort')==='oldest'      ? 'selected' : '' }}>Terlama</option>
                <option value="orders_desc" {{ request('sort')==='orders_desc' ? 'selected' : '' }}>Pesanan Terbanyak</option>
                <option value="spent_desc"  {{ request('sort')==='spent_desc'  ? 'selected' : '' }}>Belanja Terbanyak</option>
                <option value="points_desc" {{ request('sort')==='points_desc' ? 'selected' : '' }}>Poin Tertinggi</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Terapkan</button>
            @if(request()->hasAny(['search','filter','sort']))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table id="customers-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>Poin</th>
                    <th>Pesanan</th>
                    <th>Bergabung</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    <tr style="cursor:pointer;" class="{{ $customer->is_active ? '' : 'row-inactive' }}" onclick="window.location='{{ route('admin.customers.show', $customer) }}'">
                        <td>{{ $customers->firstItem() + $index }}</td>
                        <td><strong>{{ $customer->name }}</strong></td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                        <td>
                            <span class="badge-status badge-paid"><i class="fas fa-star"></i> {{ number_format($customer->points) }}</span>
                        </td>
                        <td>{{ $customer->orders_count }} pesanan</td>
                        <td>{{ $customer->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <h3>Belum ada pelanggan</h3>
                                <p>Pelanggan yang mendaftar akan muncul di sini</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="card-footer">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@push('styles')
<style>
/* --- Customers table row hover --- */
#customers-table tbody tr:hover {
    background: #eff6ff;
    box-shadow: inset 3px 0 0 var(--primary);
    transition: background 0.15s ease, box-shadow 0.15s ease;
}
#customers-table tbody tr.row-inactive {
    opacity: 0.55;
}
#customers-table tbody tr.row-inactive:hover {
    opacity: 0.85;
}

/* --- Status toggle badge --- */
.status-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
    border: 1.5px solid;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
    background: transparent;
}
.status-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.85;
    flex-shrink: 0;
    transition: background 0.18s ease;
}
.status-action { display: none; }
.status-toggle:hover .status-label { display: none; }
.status-toggle:hover .status-action { display: inline; }

.status-active  { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
.status-active:hover  { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.status-inactive { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.status-inactive:hover { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
</style>
@endpush
@endsection
