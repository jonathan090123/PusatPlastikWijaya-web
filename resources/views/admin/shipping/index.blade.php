@extends('layouts.admin')

@section('title', 'Pengaturan Pengiriman - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-truck"></i> Pengaturan Pengiriman</h1>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

{{-- 3 method cards --}}
<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

    {{-- Pickup Card --}}
    <div class="card" id="card-pickup" style="{{ $pickup->is_active ? '' : 'opacity:0.75;' }}">
        <div class="card-body">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-store" style="color:var(--success); font-size:1.2rem;"></i>
                    <strong style="font-size:1rem;">Pickup</strong>
                </div>
                <span id="badge-pickup" class="badge-status" style="background:{{ $pickup->is_active ? '#d1fae5' : '#fee2e2' }}; color:{{ $pickup->is_active ? '#065f46' : '#991b1b' }};">
                    {{ $pickup->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <p style="font-size:0.85rem; color:var(--gray-500); margin:0 0 0.5rem;">{{ $pickup->description }}</p>
            <p style="font-size:0.85rem; margin:0 0 1rem;"><strong>Biaya:</strong> <span style="color:var(--success);">Gratis</span></p>
            <button type="button"
                class="btn {{ $pickup->is_active ? 'btn-danger' : 'btn-success' }} btn-sm toggle-btn"
                data-type="pickup"
                id="toggle-pickup"
                style="width:100%;">
                <i class="fas {{ $pickup->is_active ? 'fa-power-off' : 'fa-check-circle' }}"></i>
                {{ $pickup->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </div>
    </div>

    {{-- Kurir Toko Card --}}
    <div class="card" id="card-local" style="{{ $local->is_active ? '' : 'opacity:0.75;' }}">
        <div class="card-body">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-motorcycle" style="color:var(--primary); font-size:1.2rem;"></i>
                    <strong style="font-size:1rem;">Kurir Toko</strong>
                </div>
                <span id="badge-local" class="badge-status" style="background:{{ $local->is_active ? '#d1fae5' : '#fee2e2' }}; color:{{ $local->is_active ? '#065f46' : '#991b1b' }};">
                    {{ $local->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <p style="font-size:0.85rem; color:var(--gray-500); margin:0 0 0.25rem;">Blitar dalam kota</p>
            <p style="font-size:0.85rem; margin:0 0 0.25rem;"><strong>Ongkir:</strong> Rp {{ number_format($local->cost, 0, ',', '.') }}</p>
            <p style="font-size:0.85rem; margin:0 0 1rem;"><strong>Estimasi:</strong> {{ $local->estimation ?? '-' }}</p>
            <button type="button"
                class="btn {{ $local->is_active ? 'btn-danger' : 'btn-success' }} btn-sm toggle-btn"
                data-type="local"
                id="toggle-local"
                style="width:100%;">
                <i class="fas {{ $local->is_active ? 'fa-power-off' : 'fa-check-circle' }}"></i>
                {{ $local->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </div>
    </div>

    {{-- Luar Kota Card --}}
    <div class="card" id="card-outside" style="opacity:0.75;">
        <div class="card-body">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-shipping-fast" style="color:var(--gray-400); font-size:1.2rem;"></i>
                    <strong style="font-size:1rem;">Luar Kota</strong>
                </div>
                <span id="badge-outside" class="badge-status" style="background:#fef3c7; color:#92400e;">
                    Segera Hadir
                </span>
            </div>
            <p style="font-size:0.85rem; color:var(--gray-500); margin:0 0 0.5rem;">Via ekspedisi (JNE, J&T, dll) — integrasi RajaOngkir</p>
            <p style="font-size:0.85rem; margin:0 0 1rem;"><strong>Biaya:</strong> <span style="color:var(--gray-400);">Otomatis via API</span></p>
            <button type="button" disabled class="btn btn-secondary btn-sm" style="width:100%; cursor:not-allowed;" title="Integrasi RajaOngkir belum tersedia">
                <i class="fas fa-lock"></i> Belum Tersedia
            </button>
        </div>
    </div>
</div>

{{-- Setting Ongkir Lokal --}}
<div class="card">
    <div class="card-body">
        <h3 style="font-size:1.05rem; font-weight:700; color:var(--gray-800); margin-bottom:1rem;">
            <i class="fas fa-motorcycle" style="color:var(--primary);"></i> Pengaturan Ongkir Kurir Toko
        </h3>
        <form action="{{ route('admin.shipping.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label><i class="fas fa-money-bill"></i> Biaya Ongkir (Rp) <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="cost" value="{{ old('cost', $local->cost) }}" class="{{ $errors->has('cost') ? 'is-invalid' : '' }}" placeholder="10000" min="0" step="500" required>
                    @error('cost')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Estimasi Waktu</label>
                    <input type="text" name="estimation" value="{{ old('estimation', $local->estimation) }}" placeholder="Contoh: 1-2 hari">
                    @error('estimation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Deskripsi (opsional)</label>
                <textarea name="description" rows="2" placeholder="Deskripsi tambahan untuk kurir toko...">{{ old('description', $local->description) }}</textarea>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const type = this.dataset.type;
        const self = this;
        self.disabled = true;
        self.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

        fetch('{{ route('admin.shipping.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ type: type })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Gagal mengubah status.');
                self.disabled = false;
                self.innerHTML = '<i class="fas fa-sync-alt"></i> Coba Lagi';
                return;
            }

            const isActive = data.is_active;
            const card  = document.getElementById('card-' + type);
            const badge = document.getElementById('badge-' + type);

            // Update card opacity
            card.style.opacity = isActive ? '1' : '0.75';

            // Update badge
            badge.textContent = isActive ? 'Aktif' : 'Nonaktif';
            badge.style.background = isActive ? '#d1fae5' : '#fee2e2';
            badge.style.color = isActive ? '#065f46' : '#991b1b';

            // Update button
            self.className = 'btn ' + (isActive ? 'btn-danger' : 'btn-success') + ' btn-sm toggle-btn';
            self.innerHTML = '<i class="fas ' + (isActive ? 'fa-power-off' : 'fa-check-circle') + '"></i> '
                           + (isActive ? 'Nonaktifkan' : 'Aktifkan');
            self.disabled = false;
        })
        .catch(function() {
            alert('Terjadi kesalahan. Silakan coba lagi.');
            self.disabled = false;
            self.innerHTML = '<i class="fas fa-sync-alt"></i> Coba Lagi';
        });
    });
});
</script>
@endpush
