@extends('layouts.customer')

@section('title', 'Profil Saya - Pusat Plastik Wijaya')

@section('content')
<div class="container" style="padding: 2rem 1rem;">
    <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:1.5rem; color:var(--gray-900);">
        <i class="fas fa-user"></i> Profil Saya
    </h2>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
        {{-- Info Profil --}}
        <div class="card">
            <div class="card-header"><span><i class="fas fa-user"></i> Informasi Profil</span></div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nama Lengkap <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email <span style="color:var(--danger);">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> No. Handphone <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx" required>
                        @error('phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Lokasi Kota <span style="color:var(--danger);">*</span></label>
                        <div style="display:flex; gap:0.75rem; margin-top:0.25rem;">
                            <label class="city-option {{ old('city_type', $user->city_type) === 'blitar' ? 'selected' : '' }}">
                                <input type="radio" name="city_type" value="blitar"
                                    {{ old('city_type', $user->city_type) === 'blitar' ? 'checked' : '' }} required>
                                <i class="fas fa-city"></i>
                                <span>Kota Blitar</span>
                            </label>
                            <label class="city-option {{ old('city_type', $user->city_type) === 'outside' ? 'selected' : '' }}">
                                <input type="radio" name="city_type" value="outside"
                                    {{ old('city_type', $user->city_type) === 'outside' ? 'checked' : '' }}>
                                <i class="fas fa-globe-asia"></i>
                                <span>Luar Kota Blitar</span>
                            </label>
                        </div>
                        @error('city_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-home"></i> Alamat Lengkap <span style="color:var(--danger);">*</span></label>
                        <textarea name="address" rows="3" placeholder="Contoh: Jl. Bali No. 20, RT 03/RW 05...">{{ old('address', $user->address) }}</textarea>
                        <small style="color:var(--warning); display:block; margin-top:0.35rem; font-size:0.78rem;">
                            <i class="fas fa-exclamation-triangle"></i> Pastikan alamat lengkap agar pengiriman berjalan lancar.
                        </small>
                        @error('address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <div>
            {{-- Ganti Password --}}
            <div class="card">
                <div class="card-header"><span><i class="fas fa-lock"></i> Ganti Password</span></div>
                <div class="card-body">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Password Saat Ini</label>
                            <input type="password" name="current_password" required>
                            @error('current_password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Password Baru</label>
                            <input type="password" name="password" required>
                            @error('password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-warning" style="margin-top:0.5rem;">
                            <i class="fas fa-lock"></i> Perbarui Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media (max-width: 768px) {
    .container > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
.city-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    padding: 0.7rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 600;
    font-size: 0.88rem;
    color: var(--gray-600);
}
.city-option:hover { border-color: var(--primary-light); }
.city-option.selected,
.city-option:has(input:checked) {
    border-color: var(--primary);
    background: rgba(59,130,246,0.05);
    color: var(--primary);
}
.city-option input[type="radio"] { display: none; }
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('input[name="city_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.city-option').forEach(function(opt) { opt.classList.remove('selected'); });
        this.closest('.city-option').classList.add('selected');
    });
});
</script>
@endpush
