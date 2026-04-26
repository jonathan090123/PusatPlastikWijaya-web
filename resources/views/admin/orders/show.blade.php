@extends('layouts.admin')

@section('title', 'Detail Pesanan #' . $order->invoice_number . ' - Admin')

@section('content')

{{-- ── Presence Lock Banner ─────────────────────────────────────────────── --}}
@if($lockedBy)
<div id="lockBanner" style="
    display:flex; align-items:center; gap:0.75rem;
    background:#fef3c7; border:1.5px solid #f59e0b;
    border-radius:var(--radius); padding:0.85rem 1.1rem;
    margin-bottom:1.25rem; font-size:0.875rem; color:#92400e;
    animation: lockBannerIn 0.3s ease;
">
    <span style="font-size:1.4rem; flex-shrink:0;">🔒</span>
    <div style="flex:1;">
        <strong>Pesanan ini sedang dibuka oleh {{ $lockedBy['admin_name'] }}</strong>
        sejak {{ $lockedBy['since'] }}.
        <span style="display:block; margin-top:0.2rem; font-size:0.8rem; color:#a16207;">
            Semua aksi dinonaktifkan sementara untuk mencegah konflik data.
            Halaman ini akan otomatis terbuka kembali saat admin tersebut keluar.
        </span>
    </div>
    <span id="lockCountdown" style="
        flex-shrink:0; background:#f59e0b; color:#fff;
        font-weight:700; font-size:0.75rem; padding:0.25rem 0.65rem;
        border-radius:9999px; letter-spacing:0.1em; text-align:center;
        transition: background 0.3s;
    ">●○○</span>
</div>
@endif

<div class="page-header">
    <h1><i class="fas fa-file-invoice"></i> Detail Pesanan</h1>
    <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-print"></i> Print Invoice
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
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
                            'expired'           => 'badge-expired',
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
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->recipient_phone) }}" target="_blank" rel="noopener noreferrer" style="color:var(--success); text-decoration:none; font-weight:600;">
                        <i class="fab fa-whatsapp"></i> {{ $order->recipient_phone }}
                    </a><br>
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

        {{-- Tracking Number Card (non-pickup only) --}}
        @if(!$order->shippingCost || $order->shippingCost->type !== 'pickup')
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-body">
                <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                    <i class="fas fa-search-location" style="color:var(--primary);"></i> Nomor Resi
                </h3>
                @if(session('success'))
                    <div style="margin-bottom:0.75rem; padding:0.6rem 0.85rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-sm); font-size:0.82rem; color:#166534;">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif
                @if($order->tracking_number)
                    @php
                        $resi = $order->tracking_number;
                        $shName = strtolower($order->shipping_name ?? '');
                        $trackUrl = match(true) {
                            str_contains($shName, 'jne')      => 'https://www.jne.co.id/id/tracking/trace?awb=' . $resi,
                            str_contains($shName, 'j&t') || str_contains($shName, 'jnt') => 'https://jet.co.id/id/track?awbNo=' . $resi,
                            str_contains($shName, 'tiki')     => 'https://tiki.id/tracking?searchVal=' . $resi,
                            str_contains($shName, 'sicepat')  => 'https://sicepat.com/checkAwb?awb=' . $resi,
                            str_contains($shName, 'pos')      => 'https://posindonesia.co.id/id/tracking?awb=' . $resi,
                            default                           => 'https://cekresi.com/?noresi=' . $resi,
                        };
                    @endphp
                    {{-- Resi display --}}
                    <div id="resiDisplay" style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.5rem;">
                        <div style="flex:1; padding:0.5rem 0.85rem; background:#eff6ff; border:1px solid #bfdbfe; border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:space-between; gap:0.5rem; flex-wrap:wrap;">
                            <span style="font-size:0.9rem; font-weight:700; color:var(--primary); letter-spacing:0.05em;">
                                <i class="fas fa-barcode"></i> {{ $resi }}
                            </span>
                            <a href="{{ $trackUrl }}" target="_blank" rel="noopener noreferrer"
                                style="font-size:0.78rem; font-weight:600; color:var(--primary); text-decoration:none; display:flex; align-items:center; gap:0.3rem;">
                                <i class="fas fa-external-link-alt"></i> Lacak
                            </a>
                        </div>
                        <button type="button" onclick="toggleResiEdit()" class="btn btn-sm" style="background:#f3f4f6; color:var(--gray-700); border:1.5px solid var(--gray-200); white-space:nowrap;">
                            <i class="fas fa-pencil-alt"></i> Edit
                        </button>
                        <form action="{{ route('admin.orders.updateTracking', $order) }}" method="POST" style="display:inline;"
                            onsubmit="return confirm('Hapus nomor resi ini?')">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="tracking_number" value="">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </form>
                    </div>
                    {{-- Edit form (hidden by default) --}}
                    <form id="resiEditForm" action="{{ route('admin.orders.updateTracking', $order) }}" method="POST" style="display:none; margin-bottom:0.5rem;">
                        @csrf
                        @method('PATCH')
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <input type="text" name="tracking_number"
                                value="{{ old('tracking_number', $order->tracking_number) }}"
                                placeholder="Masukkan nomor resi ekspedisi..."
                                maxlength="100"
                                style="flex:1; padding:0.5rem 0.75rem; border:1.5px solid var(--primary); border-radius:var(--radius-sm); font-size:0.9rem; font-weight:600; letter-spacing:0.04em;">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <button type="button" onclick="toggleResiEdit()" class="btn btn-sm" style="background:#f3f4f6; color:var(--gray-700); border:1.5px solid var(--gray-200);">
                                Batal
                            </button>
                        </div>
                    </form>
                @else
                    {{-- No resi yet — show input form --}}
                    <form action="{{ route('admin.orders.updateTracking', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <input type="text" name="tracking_number"
                                value="{{ old('tracking_number') }}"
                                placeholder="Masukkan nomor resi ekspedisi..."
                                maxlength="100"
                                style="flex:1; padding:0.5rem 0.75rem; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); font-size:0.9rem; font-weight:600; letter-spacing:0.04em;">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                        <p style="font-size:0.75rem; color:var(--gray-400); margin:0.4rem 0 0;">
                            <i class="fas fa-info-circle"></i>
                            Setelah disimpan, customer dapat melihat nomor resi dan melacak paket di halaman pesanan mereka.
                        </p>
                    </form>
                @endif
                <script>
                    function toggleResiEdit() {
                        var display = document.getElementById('resiDisplay');
                        var form    = document.getElementById('resiEditForm');
                        var hidden  = display.style.display === 'none';
                        display.style.display = hidden ? 'flex' : 'none';
                        form.style.display    = hidden ? 'none' : 'block';
                        if (!hidden) form.querySelector('input[type=text]').focus();
                    }
                </script>
            </div>
        </div>
        @endif

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
                        <div style="display:flex; align-items:center; gap:0.75rem; padding:0.65rem 0.75rem; background:{{ $item->is_out_of_stock ? '#fff5f5' : 'var(--gray-50)' }}; border-radius:var(--radius-sm); border: {{ $item->is_out_of_stock ? '1px solid #fca5a5' : '1px solid transparent' }}; position:relative;">
                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); overflow:hidden; flex-shrink:0; background:var(--white); border:1px solid var(--gray-200); {{ $item->is_out_of_stock ? 'opacity:0.5;' : '' }}">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" style="width:100%; height:100%; object-fit:contain; padding:2px;">
                                @else
                                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--gray-400);"><i class="fas fa-image"></i></div>
                                @endif
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:600; font-size:0.875rem; color:var(--gray-800); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; {{ $item->is_out_of_stock ? 'text-decoration:line-through; color:var(--gray-400);' : '' }}">{{ $item->product_name }}</div>
                                <div style="font-size:0.8rem; color:var(--gray-500);">{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
                                @if($item->is_out_of_stock)
                                    <div style="font-size:0.75rem; color:#dc2626; font-weight:600; margin-top:2px;">
                                        <i class="fas fa-times-circle"></i> Stok Kosong
                                        @if($item->out_of_stock_at)
                                            <span style="font-weight:400; color:var(--gray-400);">&mdash; {{ $item->out_of_stock_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.3rem;">
                                <div style="font-weight:700; font-size:0.875rem; color:{{ $item->is_out_of_stock ? 'var(--gray-400)' : 'var(--gray-800)' }}; white-space:nowrap; {{ $item->is_out_of_stock ? 'text-decoration:line-through;' : '' }}">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                                @if(!$item->is_out_of_stock && !in_array($order->status, ['completed', 'cancelled', 'expired']) && !$lockedBy)
                                    <button type="button"
                                        onclick="confirmOutOfStock({{ $item->id }}, '{{ addslashes($item->product_name) }}', '{{ route('admin.orders.items.outOfStock', [$order, $item]) }}')"
                                        style="font-size:0.7rem; padding:0.2rem 0.5rem; background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; border-radius:var(--radius-sm); cursor:pointer; white-space:nowrap;">
                                        <i class="fas fa-ban"></i> Stok Kosong
                                    </button>
                                @endif
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
                            <span>Diskon</span>
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

                    {{-- ── Refund Manual Notice ─────────────────────────────────── --}}
                @php
                    $oosItems     = $order->items->where('is_out_of_stock', true);
                    $oosSubtotal  = $oosItems->sum('subtotal');
                    $hasPaid      = $order->payment && $order->payment->isPaid();
                @endphp
                @if($oosItems->count() > 0 && $hasPaid)
                    <div style="margin-top:1rem; padding:0.85rem 1rem; background:#fffbeb; border:1.5px solid #fbbf24; border-radius:var(--radius-sm);">
                        <div style="display:flex; align-items:center; gap:0.4rem; font-weight:700; font-size:0.82rem; color:#92400e; margin-bottom:0.6rem;">
                            <i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i>
                            PERLU REFUND MANUAL
                        </div>

                        {{-- Rincian item yang perlu direfund --}}
                        <div style="font-size:0.8rem; color:#78350f; margin-bottom:0.6rem;">
                            @foreach($oosItems as $oos)
                                <div style="display:flex; justify-content:space-between; padding:0.2rem 0; border-bottom:1px dashed #fde68a;">
                                    <span style="flex:1; padding-right:0.5rem;">
                                        <i class="fas fa-times-circle" style="color:#dc2626; font-size:0.7rem;"></i>
                                        {{ $oos->product_name }}
                                        <span style="color:#a16207;">({{ $oos->quantity }} × Rp {{ number_format($oos->product_price, 0, ',', '.') }})</span>
                                    </span>
                                    <span style="font-weight:600; white-space:nowrap;">Rp {{ number_format($oos->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Total refund --}}
                        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:0.4rem;">
                            <span style="font-size:0.82rem; font-weight:600; color:#92400e;">Total Refund ke Customer</span>
                            <span style="font-size:1rem; font-weight:800; color:#dc2626;">
                                Rp {{ number_format($oosSubtotal, 0, ',', '.') }}
                            </span>
                        </div>

                        <div style="margin-top:0.6rem; font-size:0.75rem; color:#a16207; line-height:1.5;">
                            <i class="fas fa-info-circle"></i>
                            Hubungi customer <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->recipient_phone) }}" target="_blank" rel="noopener noreferrer" style="color:#b45309; text-decoration:underline; font-weight:700;"><i class="fab fa-whatsapp"></i> {{ $order->recipient_phone }}</a> dan konfirmasi pengembalian dana. Metode pembayaran
                            <strong>({{ strtoupper(str_replace('_', ' ', $order->payment->payment_type ?? '-')) }})</strong>.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Update Status --}}
        @if(!in_array($order->status, ['completed', 'cancelled']))
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-edit" style="color:var(--primary);"></i> Update Status
                    </h3>
                    @if($lockedBy)
                        <div style="padding:0.85rem; background:#fef3c7; border:1px solid #fcd34d; border-radius:var(--radius-sm); text-align:center; font-size:0.85rem; color:#92400e;">
                            <i class="fas fa-lock"></i>
                            Diblokir — <strong>{{ $lockedBy['admin_name'] }}</strong> sedang mengelola pesanan ini.
                        </div>
                    @else
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
                    @endif {{-- end @if($lockedBy) --}}
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body" style="text-align:center; padding:1.25rem; color:var(--gray-400); font-size:0.875rem;">
                    <i class="fas fa-lock" style="font-size:1.25rem; display:block; margin-bottom:0.5rem;"></i>
                    Pesanan ini sudah <strong>{{ $order->status_label }}</strong> dan tidak dapat diubah.
                </div>
            </div>
            @if($order->status === 'cancelled' && $order->payment && $order->payment->isPaid())
                @php
                    $invoiceLines = "*INVOICE PEMBATALAN*\n";
                    $invoiceLines .= "========================\n";
                    $invoiceLines .= "No. Invoice: {$order->invoice_number}\n";
                    $invoiceLines .= "Tanggal Pesan: {$order->created_at->format('d M Y, H:i')}\n";
                    $invoiceLines .= "Pelanggan: {$order->user->name}\n";
                    $invoiceLines .= "No. HP: {$order->recipient_phone}\n";
                    $invoiceLines .= "Alamat: {$order->shipping_address}\n";
                    $invoiceLines .= "========================\n";
                    $invoiceLines .= "*Detail Produk:*\n";
                    foreach($order->items as $item) {
                        $invoiceLines .= "- {$item->product_name} ({$item->quantity} x Rp " . number_format($item->product_price, 0, ',', '.') . ") = Rp " . number_format($item->subtotal, 0, ',', '.') . "\n";
                    }
                    $invoiceLines .= "========================\n";
                    $invoiceLines .= "Subtotal: Rp " . number_format($order->subtotal, 0, ',', '.') . "\n";
                    if($order->discount_amount > 0) {
                        $invoiceLines .= "Diskon: -Rp " . number_format($order->discount_amount, 0, ',', '.') . "\n";
                    }
                    if($order->points_discount > 0) {
                        $invoiceLines .= "Diskon Poin ({$order->points_used} poin): -Rp " . number_format($order->points_discount, 0, ',', '.') . "\n";
                    }
                    $invoiceLines .= "Ongkos Kirim: Rp " . number_format($order->shipping_fee, 0, ',', '.') . "\n";
                    $invoiceLines .= "*Total: Rp " . number_format($order->total, 0, ',', '.') . "*\n";
                    $invoiceLines .= "========================\n";
                    $invoiceLines .= "Metode Bayar: " . strtoupper(str_replace('_', ' ', $order->payment->payment_type ?? '-')) . "\n";
                    $invoiceLines .= "Status: DIBATALKAN\n";
                    $invoiceLines .= "\nMohon diproses refund untuk pesanan di atas. Terima kasih.";
                @endphp
                <div style="margin-top:0.75rem;">
                    <a href="https://wa.me/6282313505557?text={{ urlencode($invoiceLines) }}"
                       target="_blank" rel="noopener noreferrer"
                       style="display:flex; align-items:center; justify-content:center; gap:0.5rem; width:100%; padding:0.7rem 1rem; background:white; color:var(--primary); border:2px solid var(--primary); border-radius:var(--radius); font-weight:600; font-size:0.875rem; text-decoration:none; transition:var(--transition);">
                        <i class="fab fa-whatsapp" style="font-size:1.2rem;"></i> Kirim Invoice Pembatalan ke Accounting
                    </a>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Out-of-Stock Item Modal --}}
<div id="oosModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius-md); padding:1.75rem; max-width:380px; width:90%; box-shadow:var(--shadow-xl);">
        <div style="text-align:center; font-size:2rem; margin-bottom:0.75rem;">⚠️</div>
        <h3 style="font-size:1.05rem; font-weight:700; color:var(--gray-900); text-align:center; margin-bottom:0.5rem;">Tandai Stok Kosong?</h3>
        <p style="font-size:0.875rem; color:var(--gray-500); text-align:center; margin-bottom:1.25rem; line-height:1.6;">
            Item <strong id="oosItemName"></strong> akan ditandai <strong>stok kosong</strong> dan dicoret di invoice.<br>
            Jika pesanan sudah selesai, poin proporsional akan dikurangi.<br>
            <em>Pengembalian uang dilakukan manual oleh admin.</em>
        </p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <button id="oosCancel" class="btn btn-secondary" style="width:100%;">Batal</button>
            <button id="oosConfirm" class="btn btn-danger" style="width:100%;">Ya, Tandai Kosong</button>
        </div>
    </div>
</div>

{{-- Hidden form for OOS submit --}}
<form id="oosForm" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
</form>

@endsection

@push('styles')
<style>
@keyframes modalIn {
    from { opacity:0; transform:scale(0.93); }
    to   { opacity:1; transform:scale(1); }
}
@keyframes lockBannerIn {
    from { opacity:0; transform:translateY(-8px); }
    to   { opacity:1; transform:translateY(0); }
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

// ---- Out-of-stock per item modal ----
let oos_form_action = '';
const oosModal   = document.getElementById('oosModal');
const oosName    = document.getElementById('oosItemName');
const oosConfirm = document.getElementById('oosConfirm');
const oosCancel  = document.getElementById('oosCancel');

function confirmOutOfStock(itemId, itemName, actionUrl) {
    oos_form_action = actionUrl;
    oosName.textContent = '"' + itemName + '"';
    oosModal.style.display = 'flex';
}

oosConfirm.addEventListener('click', function () {
    const form = document.getElementById('oosForm');
    form.action = oos_form_action;
    oosModal.style.display = 'none';
    form.submit();
});

oosCancel.addEventListener('click', function () {
    oosModal.style.display = 'none';
});

oosModal.addEventListener('click', function (e) {
    if (e.target === oosModal) oosModal.style.display = 'none';
});

// ── Presence Lock: Heartbeat & Release ──────────────────────────────────────
(function () {
    const IS_LOCKER     = {{ $lockedBy ? 'false' : 'true' }};
    const HEARTBEAT_URL = '{{ route('admin.orders.heartbeat', $order) }}';
    const RELEASE_URL   = '{{ route('admin.orders.releaseLock', $order) }}';
    const CHECK_URL     = '{{ route('admin.orders.checkLock', $order) }}';
    const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const lockBanner    = document.getElementById('lockBanner');
    const countdownEl   = document.getElementById('lockCountdown');

    // POST — untuk heartbeat (renew lock)
    function postJSON(url) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            keepalive: true,
        });
    }

    // GET — untuk polling (hanya baca, tidak renew)
    function getJSON(url) {
        return fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
    }

    if (IS_LOCKER) {
        // ── Pemegang lock ────────────────────────────────────────────────────
        // Perpanjang tiap 30 detik (TTL cache = 120 detik)
        setInterval(() => postJSON(HEARTBEAT_URL), 30000);

        // Lepas lock saat halaman ditutup — sendBeacon lebih andal dari fetch di beforeunload
        window.addEventListener('beforeunload', () => {
            navigator.sendBeacon(RELEASE_URL + '?_token=' + encodeURIComponent(CSRF));
        });

        // Jika tab backgrounded lalu dibuka lagi, langsung renew
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') postJSON(HEARTBEAT_URL);
        });

    } else if (lockBanner && countdownEl) {
        // ── Admin yang "terkunci" — poll tiap 5 detik via GET (read-only) ────
        let dot = 0;
        const dots = ['●○○', '○●○', '○○●'];
        let reloading = false;

        const poll = () => {
            if (reloading) return;

            getJSON(CHECK_URL)
                .then(r => r.json())
                .then(data => {
                    if (!data.is_locked) {
                        // Lock sudah habis (grace period selesai) → Admin lain bisa akses
                        reloading = true;
                        countdownEl.textContent = '✓';
                        countdownEl.style.background = '#22c55e';
                        setTimeout(() => window.location.reload(), 300);

                    } else if (data.releasing) {
                        // Grace period: Admin 1 baru keluar, tunggu ~15 detik
                        countdownEl.textContent = '⏳';
                        countdownEl.style.background = '#f97316';
                        countdownEl.title = 'Sedang melepas kunci, harap tunggu...';

                        // Update teks banner agar lebih informatif
                        const bannerStrong = lockBanner.querySelector('strong');
                        if (bannerStrong) {
                            bannerStrong.closest('div')?.querySelector('span')?.remove();
                            bannerStrong.textContent = data.locker_name + ' baru saja keluar';
                            const hint = document.createElement('span');
                            hint.style.cssText = 'display:block; margin-top:0.2rem; font-size:0.8rem; color:#a16207;';
                            hint.textContent = 'Menunggu grace period selesai, halaman akan terbuka otomatis...';
                            if (!lockBanner.querySelector('.grace-hint')) {
                                hint.classList.add('grace-hint');
                                bannerStrong.parentElement.appendChild(hint);
                            }
                        }
                    } else {
                        // Lock masih aktif — animasi titik biasa
                        countdownEl.textContent = dots[dot % 3];
                        countdownEl.style.background = '#f59e0b';
                        countdownEl.title = '';
                        dot++;
                    }
                })
                .catch(() => { /* network error — coba lagi */ });
        };

        // Poll pertama setelah 5 detik, lalu tiap 5 detik
        setTimeout(() => { poll(); setInterval(poll, 5000); }, 5000);
    }
}());


</script>
@endpush
