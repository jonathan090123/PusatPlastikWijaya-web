@extends('layouts.admin')

@section('title', 'Detail Pesanan #' . $order->invoice_number . ' - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-file-invoice"></i> Detail Pesanan</h1>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div style="display:grid; grid-template-columns:1fr 350px; gap:1.5rem; align-items:start;">
    {{-- Left: Order Info --}}
    <div>
        {{-- Order Info Card --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3 style="font-size:1.1rem; font-weight:700; color:var(--gray-800); margin:0;">
                        {{ $order->invoice_number }}
                    </h3>
                    @php
                        $badgeClass = match($order->status) {
                            'pending'           => 'badge-pending',
                            'waiting_payment'   => 'badge-waiting_payment',
                            'paid'              => 'badge-paid',
                            'processing'        => 'badge-processing',
                            'ready_for_pickup'  => 'badge-ready-pickup',
                            'shipped'           => 'badge-shipped',
                            'completed'         => 'badge-completed',
                            'cancelled'         => 'badge-cancelled',
                            default             => '',
                        };
                    @endphp
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.2rem;">
                        <span style="font-size:0.72rem; color:var(--gray-600); font-weight:700; text-transform:uppercase; letter-spacing:0.4px;">Status Pesanan</span>
                        <span class="badge-status {{ $badgeClass }}" style="font-size:0.85rem; padding:0.4rem 0.8rem;">{{ $order->status_label }}</span>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; font-size:0.9rem; color:var(--gray-600);">
                    <div>
                        <strong style="color:var(--gray-800);">Tanggal Pesan</strong><br>
                        {{ $order->created_at->format('d M Y, H:i') }}
                    </div>
                    <div>
                        <strong style="color:var(--gray-800);">Pelanggan</strong><br>
                        {{ $order->user->name ?? '-' }} ({{ $order->user->email ?? '-' }})
                    </div>
                </div>
            </div>
        </div>

        {{-- Shipping Info Card --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-body">
                <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                    <i class="fas fa-truck" style="color:var(--primary);"></i> Informasi Pengiriman
                </h3>
                <div style="font-size:0.9rem; color:var(--gray-600); line-height:1.6;">
                    <strong>{{ $order->recipient_name }}</strong><br>
                    {{ $order->recipient_phone }}<br>
                    {{ $order->shipping_address }}<br>
                    <span style="color:var(--primary); font-weight:600;">{{ $order->shipping_name }}</span>
                    @if($order->shippingCost && $order->shippingCost->estimation)
                        <span style="color:var(--gray-400);">({{ $order->shippingCost->estimation }})</span>
                    @endif
                </div>
                @if($order->notes)
                    <div style="margin-top:0.75rem; padding:0.75rem; background:var(--gray-50); border-radius:var(--radius-sm); font-size:0.85rem;">
                        <strong>Catatan:</strong> {{ $order->notes }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Payment Info Card --}}
        @if($order->payment)
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-credit-card" style="color:var(--primary);"></i> Informasi Pembayaran
                    </h3>
                    <div style="font-size:0.88rem; color:var(--gray-600); line-height:2;">
                        <div style="display:flex; justify-content:space-between;">
                            <span>Status</span>
                            @if($order->payment->isPaid())
                                <span class="badge-status badge-paid">Sudah Dibayar</span>
                            @else
                                <span class="badge-status badge-pending">{{ ucfirst($order->payment->transaction_status ?? 'Pending') }}</span>
                            @endif
                        </div>
                        @if($order->payment->payment_type)
                            <div style="display:flex; justify-content:space-between;">
                                <span>Metode</span>
                                <strong>{{ strtoupper(str_replace('_', ' ', $order->payment->payment_type)) }}</strong>
                            </div>
                        @endif
                        @if($order->payment->transaction_id)
                            <div style="display:flex; justify-content:space-between;">
                                <span>ID Transaksi</span>
                                <strong style="font-size:0.8rem;">{{ $order->payment->transaction_id }}</strong>
                            </div>
                        @endif
                        @if($order->payment->paid_at)
                            <div style="display:flex; justify-content:space-between;">
                                <span>Dibayar pada</span>
                                <strong>{{ $order->payment->paid_at->format('d M Y, H:i') }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Right: Summary & Status Update --}}
    <div>
        {{-- Ringkasan Pesanan: items + price --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-body">
                <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                    <i class="fas fa-box" style="color:var(--primary);"></i> Ringkasan Pesanan
                </h3>

                {{-- Items --}}
                <div style="display:flex; flex-direction:column; gap:0.6rem; margin-bottom:1rem;">
                    @foreach($order->items as $item)
                        <div style="display:flex; align-items:center; gap:0.75rem; padding:0.65rem 0.75rem; background:var(--gray-50); border-radius:var(--radius-sm);">
                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); overflow:hidden; flex-shrink:0; background:var(--white); border:1px solid var(--gray-200);">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" style="width:100%; height:100%; object-fit:contain; padding:2px;">
                                @else
                                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--gray-400);"><i class="fas fa-image"></i></div>
                                @endif
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:600; font-size:0.875rem; color:var(--gray-800); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item->product_name }}</div>
                                <div style="font-size:0.8rem; color:var(--gray-500);">{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
                            </div>
                            <div style="font-weight:700; font-size:0.875rem; color:var(--gray-800); white-space:nowrap;">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Price breakdown --}}
                <div style="font-size:0.9rem; border-top:1px solid var(--gray-100); padding-top:0.75rem;">
                    <div style="display:flex; justify-content:space-between; padding:0.35rem 0; color:var(--gray-600);">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div style="display:flex; justify-content:space-between; padding:0.35rem 0; color:var(--success);">
                            <span>Diskon Voucher</span>
                            <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($order->points_discount > 0)
                        <div style="display:flex; justify-content:space-between; padding:0.35rem 0; color:var(--success);">
                            <span>Diskon Poin ({{ $order->points_used }} poin)</span>
                            <span>-Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div style="display:flex; justify-content:space-between; padding:0.35rem 0; color:var(--gray-600);">
                        <span>Ongkos Kirim</span>
                        <span>Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:0.65rem 0 0; margin-top:0.4rem; border-top:2px solid var(--gray-100); font-weight:700; font-size:1.05rem; color:var(--gray-900);">
                        <span>Total</span>
                        <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Update Status --}}
        @if(!in_array($order->status, ['completed', 'cancelled']))
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-edit" style="color:var(--primary);"></i> Update Status
                    </h3>
                    @php
                        $shippingType = $order->shippingCost->type ?? 'local';
                    @endphp
                    <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" id="statusForm">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <select name="status" id="statusSelect" required>
                                <option value="processing"         {{ $order->status === 'processing'        ? 'selected' : '' }}>Diproses</option>
                                @if($shippingType === 'pickup')
                                <option value="ready_for_pickup"   {{ $order->status === 'ready_for_pickup'  ? 'selected' : '' }}>Siap Diambil</option>
                                @else
                                <option value="shipped"            {{ $order->status === 'shipped'           ? 'selected' : '' }}>Dikirim</option>
                                @endif
                                <option value="completed"          {{ $order->status === 'completed'         ? 'selected' : '' }}>Selesai</option>
                                <option value="cancelled"          {{ $order->status === 'cancelled'         ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                        @if($shippingType === 'outside')
                            <p style="font-size:0.78rem; color:var(--gray-400); margin:0 0 0.75rem; line-height:1.5;">
                                <i class="fas fa-info-circle"></i>
                                Pengiriman luar kota — alur status sama hingga integrasi RajaOngkir aktif.
                            </p>
                        @endif
                        <button type="button" id="submitStatusBtn" class="btn btn-primary" style="width:100%;">
                            <i class="fas fa-save"></i> Perbarui Status
                        </button>
                    </form>

                    {{-- Confirmation Modal --}}
                    <div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
                        <div style="background:var(--white); border-radius:var(--radius-md); padding:1.75rem; max-width:380px; width:90%; box-shadow:var(--shadow-xl); animation:modalIn 0.15s ease;">
                            <div id="modalIcon" style="text-align:center; font-size:2.5rem; margin-bottom:0.75rem;"></div>
                            <h3 id="modalTitle" style="font-size:1.05rem; font-weight:700; color:var(--gray-900); text-align:center; margin-bottom:0.5rem;"></h3>
                            <p id="modalDesc" style="font-size:0.875rem; color:var(--gray-500); text-align:center; margin-bottom:1.25rem; line-height:1.6;"></p>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                                <button id="modalCancel" class="btn btn-secondary" style="width:100%;">Batal</button>
                                <button id="modalConfirm" class="btn btn-primary" style="width:100%;">Ya, Lanjutkan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body" style="text-align:center; padding:1.25rem; color:var(--gray-400); font-size:0.875rem;">
                    <i class="fas fa-lock" style="font-size:1.25rem; display:block; margin-bottom:0.5rem;"></i>
                    Pesanan ini sudah <strong>{{ $order->status_label }}</strong> dan tidak dapat diubah.
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@keyframes modalIn {
    from { opacity:0; transform:scale(0.93); }
    to   { opacity:1; transform:scale(1); }
}
@media (max-width: 768px) {
    .admin-content > div > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const btn    = document.getElementById('submitStatusBtn');
    const select = document.getElementById('statusSelect');
    const modal  = document.getElementById('confirmModal');
    const title  = document.getElementById('modalTitle');
    const desc   = document.getElementById('modalDesc');
    const icon   = document.getElementById('modalIcon');
    const confirmBtn = document.getElementById('modalConfirm');
    const cancelBtn  = document.getElementById('modalCancel');

    const configs = {
        completed: {
            icon: '✅',
            title: 'Tandai Pesanan Selesai?',
            desc: 'Pesanan akan ditandai <strong>Selesai</strong> dan tidak dapat diubah lagi. Pastikan barang sudah diterima pelanggan.',
            confirmClass: 'btn-success',
        },
        cancelled: {
            icon: '⚠️',
            title: 'Batalkan Pesanan?',
            desc: 'Pesanan akan dibatalkan dan <strong>tidak dapat diubah lagi</strong>. Stok produk tidak otomatis dikembalikan.',
            confirmClass: 'btn-danger',
        },
    };

    btn.addEventListener('click', function () {
        const val = select.value;
        if (configs[val]) {
            const cfg = configs[val];
            icon.textContent = cfg.icon;
            title.textContent = cfg.title;
            desc.innerHTML = cfg.desc;
            confirmBtn.className = 'btn ' + cfg.confirmClass;
            modal.style.display = 'flex';
        } else {
            document.getElementById('statusForm').submit();
        }
    });

    confirmBtn.addEventListener('click', function () {
        modal.style.display = 'none';
        document.getElementById('statusForm').submit();
    });

    cancelBtn.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.style.display = 'none';
    });
}());
</script>
@endpush
