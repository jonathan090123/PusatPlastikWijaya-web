<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem 0.5rem;
        }

        /* Print actions (hidden on print) */
        .print-actions {
            width: 100%;
            max-width: 320px;
            text-align: center;
            margin-bottom: 0.75rem;
        }

        /* Paper size selector */
        .paper-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin-bottom: 0.6rem;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 0.82rem;
            color: #374151;
        }
        .paper-selector label { font-weight: 600; }
        .paper-btn {
            padding: 0.3rem 0.75rem;
            border-radius: 6px;
            border: 1.5px solid #d1d5db;
            background: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            color: #374151;
            transition: all 0.15s;
        }
        .paper-btn.active {
            background: #1e3a5f;
            color: #fff;
            border-color: #1e3a5f;
        }

        .btn-print {
            background: #1e3a5f;
            color: #fff;
            border: none;
            padding: 0.45rem 1.25rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            margin-right: 0.35rem;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .btn-close-tab {
            background: #f3f4f6;
            color: #374151;
            border: 1.5px solid #d1d5db;
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        /* Receipt wrapper — width controlled by JS */
        .receipt {
            width: 58mm;
            font-size: 10px;
            line-height: 1.45;
            color: #000;
            background: #fff;
            padding: 4px 2px;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .bold        { font-weight: bold; }

        .divider-solid { border-top: 1px solid #000; margin: 4px 0; }
        .divider-dash  { border-top: 1px dashed #000; margin: 4px 0; }

        .store-name {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .store-sub {
            font-size: 9px;
            text-align: center;
            margin-bottom: 2px;
        }

        .section { margin: 3px 0; }
        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .row .label { flex: 1; }
        .row .value { text-align: right; flex-shrink: 0; max-width: 55%; }

        .item-name  { font-weight: bold; }
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 9.5px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 11px;
        }

        .thank-you {
            text-align: center;
            font-size: 9px;
            margin-top: 3px;
        }

        /* Dynamic page size — updated by JS before print */
        @media print {
            body { padding: 0; background: #fff; }
            .print-actions { display: none; }
        }
    </style>

    {{-- Dynamic @page size injected by JS --}}
    <style id="pageStyle">
        @page { size: 58mm auto; margin: 2mm 1mm; }
    </style>
</head>
<body>

    {{-- Screen-only print controls --}}
    <div class="print-actions">
        <div class="paper-selector">
            <label>Kertas:</label>
            <button class="paper-btn active" onclick="setPaper(58)" id="btn58">58 mm</button>
            <button class="paper-btn" onclick="setPaper(75)" id="btn75">75 mm</button>
        </div>
        <div>
            <button class="btn-print" onclick="window.print()">🖨 Cetak</button>
            <button class="btn-close-tab" onclick="window.close()">✕ Tutup</button>
        </div>
    </div>

    <div class="receipt" id="receipt">

        {{-- Store Header --}}
        <div class="store-name">PUSAT PLASTIK WIJAYA</div>
        <div class="store-sub">Supplier Plastik Berkualitas</div>
        <div class="divider-solid"></div>

        {{-- Invoice Info --}}
        <div class="section">
            <div class="row">
                <span class="label">No. Invoice</span>
                <span class="value bold">{{ $order->invoice_number }}</span>
            </div>
            <div class="row">
                <span class="label">Tanggal</span>
                <span class="value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value bold">{{ strtoupper($order->status_label) }}</span>
            </div>
        </div>
        <div class="divider-dash"></div>

        {{-- Customer --}}
        <div class="section">
            <div class="bold">PEMBELI:</div>
            <div>{{ $order->user->name ?? '-' }}</div>
            <div>{{ $order->user->phone ?? '-' }}</div>
        </div>
        <div class="divider-dash"></div>

        {{-- Shipping --}}
        <div class="section">
            <div class="bold">PENGIRIMAN:</div>
            <div>{{ $order->recipient_name }}</div>
            <div>{{ $order->recipient_phone }}</div>
            <div>{{ $order->shipping_address }}</div>
            @if($order->shipping_name)
                <div class="row" style="margin-top:2px;">
                    <span class="label">Ekspedisi</span>
                    <span class="value">{{ $order->shipping_name }}</span>
                </div>
            @endif
            @if($order->tracking_number)
                <div class="row">
                    <span class="label">No. Resi</span>
                    <span class="value bold">{{ $order->tracking_number }}</span>
                </div>
            @endif
        </div>
        <div class="divider-dash"></div>

        {{-- Items --}}
        <div class="section">
            <div class="bold">ITEM PESANAN:</div>
            @php $refundTotal = 0; @endphp
            @foreach($order->items as $item)
                @if($item->is_out_of_stock)
                    @php $refundTotal += (float)$item->subtotal; @endphp
                    <div style="margin-top:3px; opacity:0.55;">
                        <div class="item-name" style="text-decoration:line-through;">[STOK KOSONG] {{ $item->product_name }}</div>
                        <div class="item-detail" style="text-decoration:line-through;">
                            <span>{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</span>
                            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @else
                    <div style="margin-top:3px;">
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-detail">
                            <span>{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</span>
                            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
            @if($refundTotal > 0)
                <div style="margin-top:5px; padding-top:4px; border-top:1px dashed #888; font-size:0.8em;">
                    <div style="font-weight:bold;">* ITEM STOK KOSONG: Hubungi admin untuk pengembalian dana.</div>
                    <div class="item-detail">
                        <span>Total refund:</span>
                        <span>Rp {{ number_format($refundTotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif
        </div>
        <div class="divider-dash"></div>

        {{-- Price Breakdown --}}
        <div class="section">
            <div class="row">
                <span class="label">Subtotal</span>
                <span class="value">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="row">
                    <span class="label">Diskon</span>
                    <span class="value">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($order->points_discount > 0)
                <div class="row">
                    <span class="label">Diskon Poin</span>
                    <span class="value">-Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="row">
                <span class="label">Ongkir</span>
                <span class="value">Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="divider-solid"></div>

        {{-- Total --}}
        <div class="total-row">
            <span>TOTAL</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <div class="divider-solid"></div>

        {{-- Payment --}}
        @if($order->payment)
            <div class="section">
                <div class="row">
                    <span class="label">Pembayaran</span>
                    <span class="value bold">{{ $order->payment->isPaid() ? 'LUNAS' : strtoupper($order->payment->transaction_status ?? 'PENDING') }}</span>
                </div>
                @if($order->payment->payment_type)
                    <div class="row">
                        <span class="label">Metode</span>
                        <span class="value">{{ strtoupper(str_replace('_', ' ', $order->payment->payment_type)) }}</span>
                    </div>
                @endif
                @if($order->payment->paid_at)
                    <div class="row">
                        <span class="label">Tgl Bayar</span>
                        <span class="value">{{ $order->payment->paid_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
            <div class="divider-dash"></div>
        @endif

        {{-- Notes --}}
        @if($order->notes)
            <div class="section">
                <div class="bold">Catatan:</div>
                <div>{{ $order->notes }}</div>
            </div>
            <div class="divider-dash"></div>
        @endif

        {{-- Footer --}}
        <div class="thank-you">
            Terima kasih atas pesanan Anda!<br>
            Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>

    <script>
        var currentPaper = 58;

        function setPaper(mm) {
            currentPaper = mm;

            // Update receipt width
            document.getElementById('receipt').style.width = mm + 'mm';

            // Update @page size for print
            document.getElementById('pageStyle').textContent =
                '@page { size: ' + mm + 'mm auto; margin: 2mm 1mm; }';

            // Update .print-actions width
            document.querySelector('.print-actions').style.maxWidth = (mm * 1.4) + 'mm';

            // Update button active state
            document.getElementById('btn58').classList.toggle('active', mm === 58);
            document.getElementById('btn75').classList.toggle('active', mm === 75);

            // Adjust font size slightly for wider paper
            document.getElementById('receipt').style.fontSize = mm === 75 ? '11px' : '10px';
        }
    </script>

</body>
</html>
