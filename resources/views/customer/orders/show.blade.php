@extends('layouts.customer')

@section('title', 'Detail Pesanan #' . $order->invoice_number . ' - Pusat Plastik Wijaya')

@section('content')
<div style="padding: 0.5rem;">
    <div class="page-header">
        <h1><i class="fas fa-file-invoice"></i> Detail Pesanan</h1>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 350px; gap:1.5rem; align-items:start;">
        {{-- Left --}}
        <div>
            {{-- Order Status --}}
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                        <div>
                            <h3 style="font-size:1.1rem; font-weight:700; color:var(--gray-800); margin:0;">{{ $order->invoice_number }}</h3>
                            <span style="font-size:0.85rem; color:var(--gray-400);">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </div>
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
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <span style="font-size:0.8rem; color:var(--gray-700); font-weight:700;">Status:</span>
                            <span class="badge-status {{ $badgeClass }}" style="font-size:0.85rem; padding:0.4rem 0.8rem;">{{ $order->status_label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Deadline PHP vars --}}
            @if(in_array($order->status, ['pending', 'waiting_payment']))
            @php
                $payDeadline    = $order->payment_deadline ?? $order->created_at->addHours(12);
                $paySecondsLeft = max(0, (int) now()->diffInSeconds($payDeadline, false));
                $isPayExpired   = now()->gt($payDeadline);
            @endphp
            @endif

            {{-- Blue: Menunggu Pembayaran (above shipping) --}}
            @if(in_array($order->status, ['pending', 'waiting_payment']) && !$isPayExpired)
                <div class="card" id="bluePayCard" style="margin-bottom:1.5rem; border:2px solid var(--primary); background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                    <div class="card-body" style="text-align:center; padding:1.5rem;">
                        <i class="fas fa-credit-card" style="font-size:2rem; color:var(--primary); margin-bottom:0.75rem;"></i>
                        <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.5rem;">Menunggu Pembayaran</h3>
                        <p style="font-size:0.85rem; color:var(--gray-500); margin-bottom:1rem;">Segera selesaikan pembayaran sebelum pesanan kedaluwarsa.</p>
                        <a href="{{ route('payment.show', $order) }}" class="btn btn-primary" style="padding:0.7rem 2rem; font-weight:700;">
                            <i class="fas fa-lock"></i> Bayar Sekarang
                        </a>
                    </div>
                </div>
            @endif

            {{-- Shipping Info --}}
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        @if($order->shippingCost && $order->shippingCost->type === 'pickup')
                            <i class="fas fa-store" style="color:var(--success);"></i> Informasi Pengambilan
                        @else
                            <i class="fas fa-truck" style="color:var(--primary);"></i> Informasi Pengiriman
                        @endif
                    </h3>
                    <div style="font-size:0.88rem; color:var(--gray-700); line-height:1.8;">
                        <strong>{{ $order->recipient_name }}</strong><br>
                        {{ $order->recipient_phone }}<br>
                        @if($order->shippingCost && $order->shippingCost->type === 'pickup')
                            <div style="margin-top:0.5rem; padding:0.6rem 0.75rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-sm);">
                                <div style="font-size:0.8rem; font-weight:700; color:#166534; margin-bottom:0.2rem;">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Pickup Toko
                                </div>
                                <div style="color:#15803d; font-weight:600;">Pusat Plastik Wijaya</div>
                                <div style="color:var(--gray-700);">Ruko Niaga Jl. Sedap Malam Kav 8-10, Blitar</div>
                            </div>
                        @else
                            {{ $order->shipping_address }}<br>
                        @endif
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

            {{-- Tracking card: shown whenever a tracking number has been set --}}
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
            <div class="card" style="margin-bottom:1.5rem; border:2px solid var(--primary); background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                <div class="card-body" style="padding:1.25rem;">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-search-location" style="color:var(--primary);"></i> Lacak Paket
                    </h3>
                    <p style="font-size:0.82rem; color:var(--gray-500); margin:0 0 0.75rem;">
                        Paket Anda telah dikirim via <strong>{{ $order->shipping_name }}</strong>. Gunakan nomor resi di bawah untuk melacak status pengiriman.
                    </p>
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; padding:0.75rem 1rem; background:#fff; border:1.5px solid #bfdbfe; border-radius:var(--radius); flex-wrap:wrap;">
                        <div>
                            <div style="font-size:0.73rem; font-weight:600; color:var(--gray-400); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.2rem;">Nomor Resi</div>
                            <div style="font-size:1.05rem; font-weight:800; color:var(--gray-900); letter-spacing:0.08em;">{{ $resi }}</div>
                        </div>
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $resi }}').then(function(){ this.innerHTML='<i class=\'fas fa-check\'></i> Disalin'; setTimeout(function(){ document.getElementById('copyResiBtn').innerHTML='<i class=\'fas fa-copy\'></i> Salin'; },2000); }.bind(this))" id="copyResiBtn"
                                style="padding:0.5rem 0.85rem; background:#fff; border:1.5px solid var(--primary); border-radius:var(--radius-sm); color:var(--primary); font-size:0.8rem; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:0.3rem;">
                                <i class="fas fa-copy"></i> Salin
                            </button>
                            <a href="{{ $trackUrl }}" target="_blank" rel="noopener noreferrer"
                                style="padding:0.5rem 1rem; background:var(--primary); border:1.5px solid var(--primary); border-radius:var(--radius-sm); color:#fff; font-size:0.8rem; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:0.3rem;">
                                <i class="fas fa-external-link-alt"></i> Lacak Paket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @elseif(in_array($order->status, ['shipped', 'completed']) && !$order->tracking_number)
            <div class="card" style="margin-bottom:1.5rem; background:#fefce8; border:1px solid #fde68a;">
                <div class="card-body" style="padding:0.9rem 1.25rem; font-size:0.82rem; color:#92400e;">
                    <i class="fas fa-info-circle"></i>
                    Paket Anda sedang dalam proses pengiriman. Nomor resi akan ditampilkan di sini segera setelah tersedia.
                </div>
            </div>
            @endif

            {{-- Green: Selesaikan Pesanan (shipped / ready_for_pickup only) --}}
            @if(in_array($order->status, ['shipped', 'ready_for_pickup']))
            <div class="card" style="margin-bottom:1.5rem; border:1.5px solid #86efac; background:#f0fdf4;">
                <div class="card-body" style="padding:0.85rem 1.1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:0.6rem; flex:1; min-width:0;">
                        <i class="fas fa-check-circle" style="color:var(--success); font-size:1.15rem; flex-shrink:0;"></i>
                        <div>
                            <div style="font-size:0.85rem; font-weight:700; color:#166534;">
                                @if($order->status === 'ready_for_pickup') Pesanan Siap Diambil @else Pesanan Sudah Diterima? @endif
                            </div>
                            <div style="font-size:0.76rem; color:#15803d; line-height:1.4; margin-top:0.1rem;">
                                @if($order->status === 'ready_for_pickup')
                                    Klik tombol setelah Anda mengambil pesanan di toko.
                                @else
                                    Konfirmasi jika pesanan sudah diterima dengan baik.
                                @endif
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('orders.complete', $order) }}" id="completeForm" style="flex-shrink:0;">
                        @csrf
                        <button type="button" id="completeBtn"
                            style="padding:0.5rem 1rem; background:var(--success); color:#fff; font-weight:700; font-size:0.82rem; border:none; border-radius:var(--radius-sm); cursor:pointer; display:flex; align-items:center; gap:0.4rem; white-space:nowrap; transition:opacity 0.2s;">
                            <i class="fas fa-check-double"></i>
                            @if($order->status === 'ready_for_pickup') Sudah Diambil @else Selesaikan @endif
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Red: Payment Deadline Timer + Cancel (below shipping) --}}
            @if(in_array($order->status, ['pending', 'waiting_payment']))
            @if(!$isPayExpired)
            <div class="card" id="payDeadlineCard" style="margin-bottom:1.5rem; border:2px solid #dc2626; background:#fef2f2;">
                <div class="card-body" style="padding:1rem 1.25rem;">
                    <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; justify-content:space-between;">
                        <div style="flex:1;">
                            <div style="font-size:0.88rem; font-weight:700; color:#991b1b;">
                                <i class="fas fa-hourglass-half"></i> Batas Waktu Pembayaran
                            </div>
                            <div style="font-size:0.8rem; color:#b91c1c; margin-top:0.2rem;">
                                Selesaikan pembayaran sebelum
                                <strong>{{ $payDeadline->format('d M Y, H:i') }}</strong>
                            </div>
                            <div style="margin-top:0.6rem;">
                                <form method="POST" action="{{ route('orders.cancel', $order) }}" id="cancelForm">
                                    @csrf
                                    <button type="button" id="cancelBtn"
                                        style="background:transparent; border:1px solid #b91c1c; color:#b91c1c; font-size:0.78rem; font-weight:600; padding:0.3rem 0.75rem; border-radius:var(--radius-sm); cursor:pointer;">
                                        <i class="fas fa-times-circle"></i> Batalkan Pesanan
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:1.5rem; font-weight:800; color:#dc2626; font-variant-numeric:tabular-nums; letter-spacing:0.05em;" id="payCountdown">
                                {{ gmdate('H:i:s', $paySecondsLeft) }}
                            </div>
                            <div style="font-size:0.72rem; color:#ef4444; font-weight:600;">tersisa</div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            {{-- Time past but DB not yet updated (will expire on reload) --}}
            <div class="card" style="margin-bottom:1.5rem; background:#fef2f2; border:1px solid #fecaca;">
                <div class="card-body" style="padding:0.85rem 1.25rem; font-size:0.82rem; color:#b91c1c;">
                    <i class="fas fa-times-circle"></i> Batas waktu pembayaran telah habis. Pesanan ini tidak dapat dibayar.
                </div>
            </div>
            @endif
            @endif

            {{-- DB status already expired --}}
            @if($order->status === 'expired')
            <div class="card" style="margin-bottom:1.5rem; background:#fef2f2; border:1px solid #fecaca;">
                <div class="card-body" style="padding:0.85rem 1.25rem; font-size:0.82rem; color:#b91c1c;">
                    <i class="fas fa-times-circle"></i> Batas waktu pembayaran telah habis. Pesanan ini tidak dapat dibayar.
                </div>
            </div>
            @endif

            {{-- Green: Paid info --}}
            @if($order->payment && $order->payment->isPaid())
                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card-body">
                        <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                            <i class="fas fa-check-circle" style="color:var(--success);"></i> Informasi Pembayaran
                        </h3>
                        <div style="font-size:0.85rem; color:var(--gray-600); line-height:1.8;">
                            <div style="display:flex; justify-content:space-between;">
                                <span>Metode</span>
                                <strong>{{ strtoupper(str_replace('_', ' ', $order->payment->payment_type ?? '-')) }}</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>ID Transaksi</span>
                                <strong style="font-size:0.8rem;">{{ $order->payment->transaction_id ?? '-' }}</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Dibayar pada</span>
                                <strong>{{ $order->payment->paid_at ? $order->payment->paid_at->format('d M Y, H:i') : '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Right: Ringkasan Pesanan (items + price breakdown) --}}
        <div>
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-receipt" style="color:var(--primary);"></i> Ringkasan Pesanan
                    </h3>

                    {{-- Items --}}
                    <div style="display:flex; flex-direction:column; gap:0.6rem; margin-bottom:1rem;">
                        @foreach($order->items as $item)
                            <div style="display:flex; align-items:center; gap:0.75rem; padding:0.65rem; background:var(--gray-50); border-radius:var(--radius-sm);">
                                <div style="width:64px; height:64px; border-radius:var(--radius-sm); overflow:hidden; flex-shrink:0; background:var(--white); border:1px solid var(--gray-200);">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" style="width:100%; height:100%; object-fit:contain; padding:4px;">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--gray-400); font-size:1rem;"><i class="fas fa-image"></i></div>
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    @if($item->product)
                                        <a href="{{ route('products.show', $item->product->slug) }}" style="font-weight:600; font-size:0.85rem; color:var(--gray-800); text-decoration:none; display:block; line-height:1.4; transition:color 0.15s;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
                                            {{ $item->product_name }}
                                        </a>
                                    @else
                                        <div style="font-weight:600; font-size:0.85rem; color:var(--gray-800); line-height:1.4;">{{ $item->product_name }}</div>
                                    @endif
                                    <div style="font-size:0.78rem; color:var(--gray-500);">{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
                                </div>
                                <div style="font-weight:700; font-size:0.85rem; color:var(--gray-800); white-space:nowrap;">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Price Breakdown --}}
                    <div style="border-top:1px solid var(--gray-200); padding-top:0.75rem; font-size:0.9rem;">
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
                        <div style="display:flex; justify-content:space-between; padding:0.6rem 0 0; margin-top:0.4rem; border-top:2px solid var(--gray-100); font-weight:700; font-size:1.05rem; color:var(--gray-900);">
                            <span>Total</span>
                            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Earned points notice --}}
                    @php
                        $earnedHistory = $order->pointHistories->firstWhere('type', 'earned');
                    @endphp
                    @if($earnedHistory)
                        <div style="margin-top:0.75rem; padding:0.45rem 0.75rem; background:linear-gradient(135deg,#fef9c3 0%,#fefce8 100%); border:1.5px solid #fde047; border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:space-between; gap:0.5rem;">
                            <span style="font-size:0.8rem; font-weight:600; color:#854d0e; white-space:nowrap;">
                                <i class="fas fa-star" style="color:#ca8a04; font-size:0.75rem;"></i> Poin didapat
                            </span>
                            <span style="font-size:0.88rem; font-weight:800; color:#ca8a04; white-space:nowrap;">
                                +{{ number_format($earnedHistory->amount, 0, ',', '.') }} poin
                            </span>
                        </div>
                    @elseif($order->status === 'completed')
                        <div style="margin-top:0.75rem; padding:0.4rem 0.75rem; background:var(--gray-50); border:1px solid var(--gray-200); border-radius:var(--radius-sm); font-size:0.78rem; color:var(--gray-400); display:flex; align-items:center; gap:0.4rem;">
                            <i class="fas fa-star" style="font-size:0.7rem;"></i>
                            Tidak ada poin (transaksi terlalu kecil)
                        </div>
                    @elseif(!in_array($order->status, ['cancelled', 'expired']))
                        <div style="margin-top:0.75rem; padding:0.45rem 0.75rem; background:#eff6ff; border:1px solid #bfdbfe; border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:space-between; gap:0.5rem;">
                            <span style="font-size:0.8rem; color:#1d4ed8; white-space:nowrap;">
                                <i class="fas fa-star" style="font-size:0.72rem;"></i> Estimasi poin
                            </span>
                            <span style="font-size:0.88rem; font-weight:700; color:#1d4ed8; white-space:nowrap;">
                                +{{ number_format((int) floor($order->total / 100), 0, ',', '.') }} poin
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Beli Lagi --}}
            @if(!in_array($order->status, ['pending', 'waiting_payment']))
            <button type="button" id="reorderBtn"
                style="width:100%; display:flex; align-items:center; justify-content:center; gap:0.5rem; margin-top:1rem; padding:0.75rem 1rem; background:var(--primary); color:#fff; font-weight:700; font-size:0.9rem; border:none; border-radius:var(--radius); cursor:pointer; transition:background 0.2s, box-shadow 0.2s; box-shadow:0 2px 8px rgba(37,99,235,0.22);">
                <i class="fas fa-redo"></i> Beli Lagi
            </button>
            @endif

            {{-- Hubungi Admin via WhatsApp --}}
            <div class="card" style="margin-top:1rem; background:linear-gradient(135deg,#eff6ff 0%,#f0f9ff 100%);">
                <div class="card-body" style="padding:1rem 1.25rem;">
                    <div style="font-size:0.82rem; color:var(--gray-700); margin-bottom:0.65rem;">
                        <div style="font-weight:700; color:var(--primary); margin-bottom:0.15rem;">
                            <i class="fas fa-headset" style="color:var(--primary);"></i> Butuh bantuan?
                        </div>
                        Ada pertanyaan tentang pesanan ini? Hubungi admin kami.
                    </div>
                    <a href="https://wa.me/6282313505557?text={{ urlencode('Halo admin, saya ingin menanyakan pesanan saya dengan no. ' . $order->invoice_number) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="btn-wa-admin" style="width:100%; justify-content:center;">
                        <i class="fab fa-whatsapp" style="font-size:1.2rem;"></i> Hubungi Admin via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payment Success Modal --}}
@if(session('payment_success'))
<div id="paymentSuccessModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; display:flex; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:1.25rem; padding:2.5rem 2rem; max-width:420px; width:100%; text-align:center; box-shadow:0 25px 60px rgba(0,0,0,0.25); animation:popIn 0.35s cubic-bezier(0.34,1.56,0.64,1);">
        <div style="width:80px; height:80px; background:linear-gradient(135deg,#34d399,#059669); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; box-shadow:0 8px 20px rgba(5,150,105,0.3);">
            <i class="fas fa-check" style="font-size:2rem; color:#fff;"></i>
        </div>
        <h2 style="font-size:1.4rem; font-weight:800; color:#064e3b; margin-bottom:0.5rem;">Pembayaran Berhasil!</h2>
        <p style="font-size:0.9rem; color:#6b7280; line-height:1.7; margin-bottom:0.5rem;">
            Terima kasih! Pesanan <strong>{{ $order->invoice_number }}</strong> Anda telah dikonfirmasi dan sedang diproses oleh tim kami.
        </p>
        <p style="font-size:0.85rem; color:#9ca3af; margin-bottom:1.75rem;">
            Kami akan segera menangani pesanan Anda 🎉
        </p>
        <button onclick="document.getElementById('paymentSuccessModal').remove()" class="btn btn-primary" style="padding:0.75rem 2.5rem; font-weight:700; font-size:0.95rem;">
            Lihat Detail Pesanan
        </button>
    </div>
</div>
<style>
@keyframes popIn {
    from { opacity:0; transform:scale(0.8); }
    to   { opacity:1; transform:scale(1); }
}
</style>
@endif

{{-- ── Cancel Confirmation Modal ── --}}
<div id="cancelModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:14px; padding:2rem 1.75rem 1.75rem; max-width:360px; width:100%; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.15); animation:cancelPopIn 0.25s cubic-bezier(0.34,1.56,0.64,1);">
        <div style="width:52px; height:52px; border-radius:50%; background:#fef2f2; border:2px solid #fecaca; display:flex; align-items:center; justify-content:center; margin:0 auto 1.1rem;">
            <i class="fas fa-times" style="color:#ef4444; font-size:1.2rem;"></i>
        </div>
        <h3 style="font-size:1.1rem; font-weight:800; color:#111827; margin-bottom:0.45rem;">Batalkan Pesanan?</h3>
        <p style="font-size:0.85rem; color:#6b7280; line-height:1.65; margin-bottom:1.5rem;">
            Pesanan <strong style="color:#111827;">{{ $order->invoice_number }}</strong> akan dibatalkan secara permanen.
        </p>
        <div style="display:flex; gap:0.65rem;">
            <button type="button" id="cancelModalClose"
                style="flex:1; padding:0.65rem; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-weight:700; font-size:0.88rem; cursor:pointer;">
                Tidak
            </button>
            <button type="button" id="cancelModalConfirm"
                style="flex:1; padding:0.65rem; border-radius:8px; border:none; background:#ef4444; color:#fff; font-weight:700; font-size:0.88rem; cursor:pointer;">
                Ya, Batalkan
            </button>
        </div>
    </div>
</div>

{{-- ── Complete Confirmation Modal ── --}}
@if(in_array($order->status, ['shipped', 'ready_for_pickup']))
<div id="completeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:14px; padding:2rem 1.75rem 1.75rem; max-width:360px; width:100%; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.15); animation:cancelPopIn 0.25s cubic-bezier(0.34,1.56,0.64,1);">
        <div style="width:52px; height:52px; border-radius:50%; background:#f0fdf4; border:2px solid #86efac; display:flex; align-items:center; justify-content:center; margin:0 auto 1.1rem;">
            <i class="fas fa-check-double" style="color:#16a34a; font-size:1.2rem;"></i>
        </div>
        <h3 style="font-size:1.1rem; font-weight:800; color:#111827; margin-bottom:0.45rem;">
            @if($order->status === 'ready_for_pickup') Sudah Diambil? @else Pesanan Sudah Diterima? @endif
        </h3>
        <p style="font-size:0.85rem; color:#6b7280; line-height:1.65; margin-bottom:1.5rem;">
            @if($order->status === 'ready_for_pickup')
                Pastikan Anda sudah mengambil pesanan <strong style="color:#111827;">{{ $order->invoice_number }}</strong> di toko sebelum mengkonfirmasi.
            @else
                Pastikan pesanan <strong style="color:#111827;">{{ $order->invoice_number }}</strong> sudah diterima dengan baik sebelum mengkonfirmasi.
            @endif
        </p>
        <div style="display:flex; gap:0.65rem;">
            <button type="button" id="completeModalClose"
                style="flex:1; padding:0.65rem; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-weight:700; font-size:0.88rem; cursor:pointer;">
                Belum
            </button>
            <button type="button" id="completeModalConfirm"
                style="flex:1; padding:0.65rem; border-radius:8px; border:none; background:#16a34a; color:#fff; font-weight:700; font-size:0.88rem; cursor:pointer;">
                Ya, Selesaikan
            </button>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
.btn-wa-admin {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: var(--primary);
    color: #fff;
    font-weight: 700;
    border-radius: var(--radius);
    padding: 0.65rem 1.5rem;
    font-size: 0.88rem;
    text-decoration: none;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(37,99,235,0.25);
}
.btn-wa-admin:hover {
    background: var(--primary-dark);
    box-shadow: 0 4px 14px rgba(37,99,235,0.4);
    color: #fff;
}
@keyframes cancelPopIn {
    from { opacity:0; transform:scale(0.9); }
    to   { opacity:1; transform:scale(1); }
}
@keyframes toastIn {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
}
#show-reorder-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
#show-reorder-modal.show { display: flex; }
#show-reorder-modal-box {
    background: #fff;
    border-radius: 14px;
    padding: 1.75rem 1.5rem 1.5rem;
    max-width: 380px;
    width: 100%;
    box-shadow: 0 16px 40px rgba(0,0,0,0.15);
    animation: cancelPopIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
#show-reorder-modal-list {
    list-style: none;
    padding: 0;
    margin: 0.75rem 0 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
#show-reorder-modal-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.83rem;
    color: #7f1d1d;
    line-height: 1.5;
}
#show-reorder-modal-list li i { color: #ef4444; margin-top: 2px; flex-shrink: 0; }
@media (max-width: 768px) {
    /* Collapse the 1fr 350px grid to single column */
    div[style*="grid-template-columns:1fr 350px"],
    div[style*="grid-template-columns: 1fr 350px"] {
        grid-template-columns: 1fr !important;
    }
}
@media (max-width: 480px) {
    .btn-wa-admin { font-size: 0.82rem; padding: 0.6rem 1rem; }
    .page-header h1 { font-size: 1.15rem; }
}
</style>
@endpush

@push('scripts')
{{-- Stok Tidak Mencukupi Modal --}}
<div id="show-reorder-modal">
    <div id="show-reorder-modal-box">
        <div style="text-align:center; margin-bottom:0.75rem;">
            <div style="width:52px;height:52px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 0.85rem;">
                <i class="fas fa-exclamation-triangle" style="color:#ef4444;font-size:1.2rem;"></i>
            </div>
            <h3 style="font-size:1rem;font-weight:800;color:#111827;margin:0 0 0.3rem;">Stok Tidak Mencukupi</h3>
            <p style="font-size:0.83rem;color:#6b7280;margin:0;">Produk berikut tidak dapat ditambahkan karena stok tidak mencukupi:</p>
        </div>
        <ul id="show-reorder-modal-list"></ul>
        <button id="show-reorder-modal-ok" style="width:100%;padding:0.65rem;border-radius:8px;border:none;background:var(--primary);color:#fff;font-weight:700;font-size:0.9rem;cursor:pointer;">
            Mengerti
        </button>
    </div>
</div>

<script>
(function () {
    var countdownEl  = document.getElementById('payCountdown');
    var deadlineCard = document.getElementById('payDeadlineCard');
    var cancelBtn    = document.getElementById('cancelBtn');
    var cancelForm   = document.getElementById('cancelForm');

    var cancelModal        = document.getElementById('cancelModal');
    var cancelModalClose   = document.getElementById('cancelModalClose');
    var cancelModalConfirm = document.getElementById('cancelModalConfirm');

    if (cancelBtn && cancelModal) {
        cancelBtn.addEventListener('click', function () {
            cancelModal.style.display = 'flex';
        });
        cancelModalClose.addEventListener('click', function () {
            cancelModal.style.display = 'none';
        });
        cancelModal.addEventListener('click', function (e) {
            if (e.target === cancelModal) cancelModal.style.display = 'none';
        });
        cancelModalConfirm.addEventListener('click', function () {
            this.disabled = true;
            this.textContent = 'Membatalkan...';
            cancelForm.submit();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cancelModal.style.display = 'none';
        });
    }

    // ── Selesaikan Pesanan ──
    var completeBtn         = document.getElementById('completeBtn');
    var completeForm        = document.getElementById('completeForm');
    var completeModal       = document.getElementById('completeModal');
    var completeModalClose  = document.getElementById('completeModalClose');
    var completeModalConfirm = document.getElementById('completeModalConfirm');

    if (completeBtn && completeModal) {
        completeBtn.addEventListener('click', function () {
            completeModal.style.display = 'flex';
        });
        completeModalClose.addEventListener('click', function () {
            completeModal.style.display = 'none';
        });
        completeModal.addEventListener('click', function (e) {
            if (e.target === completeModal) completeModal.style.display = 'none';
        });
        completeModalConfirm.addEventListener('click', function () {
            this.disabled = true;
            this.textContent = 'Memproses...';
            completeForm.submit();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && completeModal.style.display === 'flex') completeModal.style.display = 'none';
        });
    }

    // ── Beli Lagi (Reorder) ──
    var reorderBtn = document.getElementById('reorderBtn');
    var srModal    = document.getElementById('show-reorder-modal');
    var srList     = document.getElementById('show-reorder-modal-list');
    var srOkBtn    = document.getElementById('show-reorder-modal-ok');

    if (srModal) {
        function closeSrModal() { srModal.classList.remove('show'); }
        srOkBtn.addEventListener('click', closeSrModal);
        srModal.addEventListener('click', function(e) { if (e.target === srModal) closeSrModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSrModal(); });
    }

    function showStockModal(insufficient) {
        srList.innerHTML = '';
        insufficient.forEach(function(item) {
            var li = document.createElement('li');
            var avail = item.available > 0
                ? 'Tersisa <strong>' + item.available + ' ' + item.unit + '</strong>'
                : 'Stok habis';
            li.innerHTML = '<i class="fas fa-box-open"></i><span><strong>' + item.name + '</strong> &mdash; ' + avail + '</span>';
            srList.appendChild(li);
        });
        srModal.classList.add('show');
    }

    if (!countdownEl) {
        initReorder();
        return;
    }

    var seconds = {{ $paySecondsLeft ?? 0 }};

    var timer = setInterval(function () {
        seconds--;
        if (seconds <= 0) {
            clearInterval(timer);
            // Call server to mark order as expired
            var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            fetch('{{ route('orders.expire', $order) }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'}
            }).finally(function () {
                setTimeout(function () { window.location.reload(); }, 800);
            });
            // Immediately show waiting message while page reloads
            var bluePayCard = document.getElementById('bluePayCard');
            if (bluePayCard) bluePayCard.style.display = 'none';
            if (deadlineCard) {
                deadlineCard.style.borderColor = '#fecaca';
                deadlineCard.innerHTML = '<div class="card-body" style="padding:1rem 1.25rem; text-align:center; color:#92400e; font-size:0.85rem;"><i class="fas fa-clock"></i> Waktu habis. Memperbarui status pesanan...</div>';
            }
            return;
        }
        var h = Math.floor(seconds / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        var s = seconds % 60;
        countdownEl.textContent =
            (h < 10 ? '0' : '') + h + ':' +
            (m < 10 ? '0' : '') + m + ':' +
            (s < 10 ? '0' : '') + s;
    }, 1000);

    initReorder();

    function initReorder() {
        if (!reorderBtn) return;
        reorderBtn.addEventListener('click', function () {
            var btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            fetch('{{ route('orders.reorder', $order) }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'}
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i> Beli Lagi';

                if (data.success) {
                    if (data.insufficient && data.insufficient.length > 0) {
                        // partial: some added, some not → show modal
                        showStockModal(data.insufficient);
                    } else {
                        // all added → redirect to cart
                        window.location.href = data.redirect || '{{ route('cart.index') }}';
                    }
                } else {
                    showReorderToast('Gagal', data.message || 'Produk tidak tersedia.', 'error');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i> Beli Lagi';
                showReorderToast('Error', 'Terjadi kesalahan, coba lagi.', 'error');
            });
        });
    }

    function showReorderToast(title, body, type) {
        var existing = document.getElementById('reorderToast');
        if (existing) existing.remove();

        var bg   = type === 'success' ? '#16a34a' : '#dc2626';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';

        var toast = document.createElement('div');
        toast.id = 'reorderToast';
        toast.innerHTML =
            '<i class="fas ' + icon + '" style="font-size:1.15rem;"></i>' +
            '<div><strong>' + title + '</strong>' + body + '</div>' +
            '<button onclick="this.parentElement.remove()" style="background:none;border:none;color:#fff;font-size:1.1rem;cursor:pointer;padding:0;line-height:1;">&times;</button>';
        toast.style.cssText =
            'position:fixed;bottom:1.5rem;right:1.5rem;z-index:99999;' +
            'display:flex;align-items:flex-start;gap:0.75rem;' +
            'background:' + bg + ';color:#fff;' +
            'padding:1rem 1.25rem;border-radius:12px;' +
            'box-shadow:0 8px 24px rgba(0,0,0,0.18);' +
            'max-width:320px;font-size:0.88rem;line-height:1.5;' +
            'animation:toastIn 0.3s ease;';

        document.body.appendChild(toast);
        setTimeout(function () {
            if (toast.parentElement) toast.remove();
        }, 5000);
    }
})();
</script>
@endpush
