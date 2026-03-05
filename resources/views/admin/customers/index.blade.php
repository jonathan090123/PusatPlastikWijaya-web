@extends('layouts.admin')

@section('title', 'Manajemen Pelanggan - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-users"></i> Manajemen Pelanggan</h1>
</div>

{{-- Search --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0.75rem 1.25rem;">
        <form action="{{ route('admin.customers.index') }}" method="GET" style="display:flex; gap:0.75rem; align-items:center;">
            <div class="form-group" style="flex:1; margin:0;">
                <input type="text" name="search" placeholder="Cari nama, email, atau HP..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
            @if(request('search'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
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
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>Poin</th>
                    <th>Pesanan</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $index }}</td>
                        <td><strong>{{ $customer->name }}</strong></td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                        <td>
                            <span class="badge-status badge-paid"><i class="fas fa-star"></i> {{ number_format($customer->points) }}</span>
                        </td>
                        <td>{{ $customer->orders_count }} pesanan</td>
                        <td>{{ $customer->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-icon btn-primary" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
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
@endsection
