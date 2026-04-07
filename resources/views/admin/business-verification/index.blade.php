@extends('layouts.admin')

@section('title', 'Verifikasi Akun Bisnis - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-building"></i> Verifikasi Akun Bisnis</h1>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1rem; padding:0.75rem 1rem; background:#d1fae5; border:1px solid #6ee7b7; border-radius:var(--radius-sm); color:#065f46; font-size:0.875rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

{{-- Filter --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-body" style="padding:0.75rem 1.25rem;">
        <form action="{{ route('admin.business-verification.index') }}" method="GET"
              style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <select name="status" style="height:38px; border:1px solid var(--gray-200); border-radius:var(--radius-sm); padding:0 0.75rem; font-size:0.85rem; background:#fff; color:var(--gray-700); min-width:180px;">
                <option value="">Semua Status</option>
                <option value="pending"  {{ request('status')==='pending'   ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="approved" {{ request('status')==='approved'  ? 'selected' : '' }}>Sudah Diverifikasi</option>
                <option value="rejected" {{ request('status')==='rejected'  ? 'selected' : '' }}>Ditolak</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Terapkan</button>
            @if(request()->filled('status'))
                <a href="{{ route('admin.business-verification.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
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
                    <th>Nama Customer</th>
                    <th>Nama Bisnis</th>
                    <th>No. HP</th>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    @php
                        $nameLower  = strtolower(trim($customer->business_name ?? ''));
                        $isDuplicate = $customer->business_verified !== 'approved'
                                       && in_array($nameLower, $approvedBusinessNames);
                    @endphp
                    <tr>
                        <td>{{ $customers->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('admin.customers.show', $customer) }}" style="color:var(--primary); font-weight:600;">
                                {{ $customer->name }}
                            </a>
                        </td>
                        <td>
                            <span style="font-weight:600;">{{ $customer->business_name ?? '-' }}</span>
                            @if($isDuplicate)
                                <span title="Nama bisnis ini sudah terdaftar dan diverifikasi sebelumnya. Periksa keasliannya sebelum approve."
                                      style="display:inline-flex; align-items:center; gap:0.25rem; margin-left:0.4rem; background:#fef3c7; color:#92400e; font-size:0.72rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px; border:1px solid #fcd34d; cursor:help;">
                                    <i class="fas fa-exclamation-triangle"></i> Duplikat
                                </span>
                            @endif
                        </td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                        <td>{{ $customer->created_at->format('d M Y') }}</td>
                        <td>
                            @if($customer->business_verified === 'pending')
                                <span class="badge-status" style="background:#fef3c7; color:#92400e; border:1px solid #fcd34d;">
                                    <i class="fas fa-clock"></i> Menunggu
                                </span>
                            @elseif($customer->business_verified === 'approved')
                                <span class="badge-status badge-paid">
                                    <i class="fas fa-check-circle"></i> Diverifikasi
                                </span>
                            @else
                                <span class="badge-status badge-cancelled">
                                    <i class="fas fa-times-circle"></i> Ditolak
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($customer->business_verified === 'pending')
                                <div style="display:flex; gap:0.4rem;">
                                    <form id="form-approve-{{ $customer->id }}" action="{{ route('admin.business-verification.approve', $customer) }}" method="POST">
                                        @csrf @method('PATCH')
                                    </form>
                                    <form id="form-reject-{{ $customer->id }}" action="{{ route('admin.business-verification.reject', $customer) }}" method="POST">
                                        @csrf @method('PATCH')
                                    </form>
                                    <button type="button" class="btn btn-primary btn-sm"
                                            onclick="openModal('approve', {{ $customer->id }}, '{{ addslashes($customer->business_name) }}', '{{ addslashes($customer->name) }}')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-sm" style="background:#fca5a5; color:#7f1d1d; border:none; cursor:pointer; padding:0.35rem 0.75rem; border-radius:var(--radius-sm); font-size:0.8rem;"
                                            onclick="openModal('reject', {{ $customer->id }}, '{{ addslashes($customer->business_name) }}', '{{ addslashes($customer->name) }}')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                            @elseif($customer->business_verified === 'approved')
                                <form id="form-reject-{{ $customer->id }}" action="{{ route('admin.business-verification.reject', $customer) }}" method="POST">
                                    @csrf @method('PATCH')
                                </form>
                                <button type="button" class="btn btn-sm" style="background:#fca5a5; color:#7f1d1d; border:none; cursor:pointer; padding:0.35rem 0.75rem; border-radius:var(--radius-sm); font-size:0.8rem;"
                                        onclick="openModal('cabut', {{ $customer->id }}, '{{ addslashes($customer->business_name) }}', '{{ addslashes($customer->name) }}')">
                                    <i class="fas fa-times"></i> Cabut
                                </button>
                            @elseif($customer->business_verified === 'rejected')
                                <form id="form-approve-{{ $customer->id }}" action="{{ route('admin.business-verification.approve', $customer) }}" method="POST">
                                    @csrf @method('PATCH')
                                </form>
                                <button type="button" class="btn btn-primary btn-sm"
                                        onclick="openModal('approve-ulang', {{ $customer->id }}, '{{ addslashes($customer->business_name) }}', '{{ addslashes($customer->name) }}')">
                                    <i class="fas fa-redo"></i> Approve Ulang
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-building"></i>
                                <h3>Tidak ada data</h3>
                                <p>Belum ada customer yang mendaftar sebagai akun bisnis</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div style="padding:1rem 1.25rem; border-top:1px solid var(--gray-100);">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection

{{-- Modal Konfirmasi Approve / Reject --}}
<div id="biz-confirm-modal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
    {{-- Overlay --}}
    <div onclick="closeModal()" style="position:absolute; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);"></div>

    {{-- Dialog --}}
    <div style="position:relative; background:#fff; border-radius:var(--radius); padding:2rem 1.75rem 1.5rem; width:100%; max-width:420px; margin:1rem; box-shadow:0 20px 60px rgba(0,0,0,0.2);">

        {{-- Icon --}}
        <div id="modal-icon" style="text-align:center; margin-bottom:1rem; font-size:2.25rem;"></div>

        {{-- Title --}}
        <h3 id="modal-title" style="text-align:center; margin:0 0 0.5rem; font-size:1.1rem; font-weight:700; color:var(--gray-800);"></h3>

        {{-- Body --}}
        <p id="modal-body" style="text-align:center; color:var(--gray-600); font-size:0.875rem; margin:0 0 1.5rem; line-height:1.55;"></p>

        {{-- Buttons --}}
        <div style="display:flex; gap:0.75rem; justify-content:center;">
            <button type="button" onclick="closeModal()"
                    style="flex:1; padding:0.6rem 1rem; border:1px solid var(--gray-200); border-radius:var(--radius-sm); background:#fff; color:var(--gray-700); font-size:0.875rem; font-weight:600; cursor:pointer;">
                Batal
            </button>
            <button id="modal-confirm-btn" type="button" onclick="submitModal()"
                    style="flex:1; padding:0.6rem 1rem; border:none; border-radius:var(--radius-sm); font-size:0.875rem; font-weight:600; cursor:pointer; color:#fff;">
                Konfirmasi
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let _modalFormId   = null;
let _modalAction   = null;

const modalConfig = {
    'approve': {
        icon:   '<i class="fas fa-check-circle" style="color:#10b981;"></i>',
        title:  'Verifikasi Akun Bisnis',
        body:   (biz, name) => `Setujui akun bisnis <strong>${biz}</strong> milik <strong>${name}</strong>?`,
        btnBg:  '#10b981',
        formPrefix: 'form-approve-',
    },
    'reject': {
        icon:   '<i class="fas fa-times-circle" style="color:#ef4444;"></i>',
        title:  'Tolak Akun Bisnis',
        body:   (biz, name) => `Tolak pendaftaran akun bisnis <strong>${biz}</strong> milik <strong>${name}</strong>?`,
        btnBg:  '#ef4444',
        formPrefix: 'form-reject-',
    },
    'cabut': {
        icon:   '<i class="fas fa-ban" style="color:#f59e0b;"></i>',
        title:  'Cabut Verifikasi',
        body:   (biz, name) => `Cabut verifikasi akun bisnis <strong>${biz}</strong> milik <strong>${name}</strong>?`,
        btnBg:  '#f59e0b',
        formPrefix: 'form-reject-',
    },
    'approve-ulang': {
        icon:   '<i class="fas fa-redo" style="color:#3b82f6;"></i>',
        title:  'Approve Ulang',
        body:   (biz, name) => `Approve ulang akun bisnis <strong>${biz}</strong> milik <strong>${name}</strong>?`,
        btnBg:  '#3b82f6',
        formPrefix: 'form-approve-',
    },
};

function openModal(action, customerId, bizName, ownerName) {
    const cfg  = modalConfig[action];
    _modalFormId = cfg.formPrefix + customerId;
    _modalAction = action;

    document.getElementById('modal-icon').innerHTML  = cfg.icon;
    document.getElementById('modal-title').textContent = cfg.title;
    document.getElementById('modal-body').innerHTML   = cfg.body(bizName, ownerName);
    document.getElementById('modal-confirm-btn').style.background = cfg.btnBg;

    const modal = document.getElementById('biz-confirm-modal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('biz-confirm-modal').style.display = 'none';
    document.body.style.overflow = '';
    _modalFormId = null;
}

function submitModal() {
    if (_modalFormId) {
        const form = document.getElementById(_modalFormId);
        if (form) form.submit();
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endpush
