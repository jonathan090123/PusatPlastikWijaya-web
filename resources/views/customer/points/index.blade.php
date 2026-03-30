@extends('layouts.customer')

@section('title', 'Poin Saya - Pusat Plastik Wijaya')

@section('content')
<div style="padding: 0.5rem;">
    <div class="page-header">
        <h1><i class="fas fa-star"></i> Poin Saya</h1>
    </div>

    {{-- Balance Card --}}
    <div class="card" style="margin-bottom:1.5rem; background:linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%); color:#fff; border:none;">
        <div class="card-body" style="padding:2rem; display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
            <div style="width:72px; height:72px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-star" style="font-size:2rem; color:#fde68a;"></i>
            </div>
            <div>
                <div style="font-size:0.9rem; font-weight:500; opacity:0.85; margin-bottom:0.25rem;">
                    Total Poin Kamu
                </div>
                <div style="font-size:3rem; font-weight:800; line-height:1; letter-spacing:-1px; color:#fde68a;">
                    {{ number_format($user->points, 0, ',', '.') }}
                    <span style="font-size:1.2rem; font-weight:600; opacity:0.8; color:#fff;"> poin</span>
                </div>
                <div style="font-size:0.8rem; opacity:0.75; margin-top:0.5rem;">
                    <i class="fas fa-info-circle"></i>
                    Kamu mendapatkan <strong>1 poin</strong> untuk setiap <strong>Rp 1.000</strong> belanja yang selesai.
                </div>
            </div>
        </div>
    </div>

    {{-- How-to-use banner --}}
    <div class="card" style="margin-bottom:1.5rem; border-left:4px solid #ca8a04; background:#fffbeb;">
        <div class="card-body" style="padding:1rem 1.25rem; display:flex; align-items:flex-start; gap:0.85rem;">
            <i class="fas fa-lightbulb" style="color:#ca8a04; font-size:1.25rem; margin-top:0.1rem; flex-shrink:0;"></i>
            <div style="font-size:0.88rem; color:#78350f; line-height:1.6;">
                <strong style="display:block; margin-bottom:0.2rem; color:#854d0e;">Cara pakai poin?</strong>
                Poin kamu bisa dipakai sebagai <strong>potongan harga</strong> saat checkout.
                Setiap <strong>1 poin = Rp 1</strong> diskon. Aktifkan toggle
                <span style="display:inline-flex; align-items:center; gap:0.25rem; background:#fef9c3; border:1px solid #fde047; border-radius:999px; padding:0.1rem 0.5rem; font-weight:700; font-size:0.78rem; color:#854d0e;">
                    <i class="fas fa-star" style="font-size:0.65rem; color:#ca8a04;"></i> Gunakan Poin
                </span>
                di halaman checkout dan pilih berapa poin yang ingin digunakan.
            </div>
        </div>
    </div>

    {{-- History Table --}}
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-history"></i> Riwayat Poin</span>
        </div>
        <div class="card-body" style="padding:0;">
            @if($histories->count() > 0)
                <div style="overflow-x:auto;">
                    <table class="data-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>No. Pesanan</th>
                                <th style="text-align:right;">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($histories as $history)
                                <tr>
                                    <td style="white-space:nowrap; color:var(--gray-500); font-size:0.85rem;">
                                        {{ $history->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td>{{ $history->description ?? ($history->type === 'earned' ? 'Poin diperoleh' : 'Poin digunakan') }}</td>
                                    <td>
                                        @if($history->order)
                                            <a href="{{ route('orders.show', $history->order) }}"
                                               style="color:var(--primary); font-weight:600; text-decoration:none;">
                                                {{ $history->order->invoice_number }}
                                            </a>
                                        @else
                                            <span style="color:var(--gray-400);">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right; font-weight:700; white-space:nowrap;">
                                        @if($history->type === 'earned' || $history->type === 'refunded')
                                            <span style="color:#16a34a;">
                                                <i class="fas fa-plus-circle" style="font-size:0.75rem;"></i>
                                                +{{ number_format($history->amount, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span style="color:var(--danger);">
                                                <i class="fas fa-minus-circle" style="font-size:0.75rem;"></i>
                                                -{{ number_format($history->amount, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($histories->hasPages())
                    <div style="padding:1rem 1.25rem; border-top:1px solid var(--gray-200);">
                        {{ $histories->links() }}
                    </div>
                @endif
            @else
                <div style="padding:3rem; text-align:center; color:var(--gray-400);">
                    <i class="fas fa-star" style="font-size:2.5rem; margin-bottom:0.75rem; display:block; opacity:0.3;"></i>
                    <p style="font-size:1rem; font-weight:600; color:var(--gray-500);">Belum ada riwayat poin</p>
                    <p style="font-size:0.85rem; margin-top:0.25rem;">Selesaikan pesanan untuk mulai mengumpulkan poin.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary" style="margin-top:1rem; display:inline-flex;">
                        <i class="fas fa-store"></i> Belanja Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
