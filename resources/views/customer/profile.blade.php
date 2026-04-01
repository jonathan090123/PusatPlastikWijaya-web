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
                            <i class="fas fa-exclamation-triangle"></i> Pastikan alamat sesuai dan lengkap.
                        </small>
                        @error('address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Tipe Pelanggan --}}
                    <div class="form-group">
                        <p style="font-size:0.75rem; color:var(--gray-400); margin-bottom:0.4rem; margin-top:-0.25rem;"><b>Opsional</b> - lewati jika Anda bukan dari usaha/perusahaan</p>
                        <label class="business-check-label {{ old('is_business', $user->customer_type === 'business' ? '1' : '') ? 'active' : '' }}">
                            <input type="checkbox" id="is_business" name="is_business" value="1"
                                {{ old('is_business', $user->customer_type === 'business' ? '1' : '') ? 'checked' : '' }}>
                            <span>Saya mewakili usaha / perusahaan</span>
                        </label>
                    </div>

                    {{-- Info Bisnis --}}
                    <div id="businessGroup" style="{{ old('is_business', $user->customer_type === 'business' ? '1' : '') ? '' : 'display:none;' }}">
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Nama Usaha / Perusahaan</label>
                            <input type="text" name="business_name"
                                   value="{{ old('business_name', $user->business_name) }}"
                                   placeholder="Contoh: PT Maju Bersama / Toko Sari Plastik"
                                   class="@error('business_name') is-invalid @enderror">
                            @error('business_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
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
.customer-type-option {
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
.customer-type-option:hover { border-color: var(--primary-light); }
.customer-type-option.selected,
.customer-type-option:has(input:checked) {
    border-color: var(--primary);
    background: rgba(59,130,246,0.05);
    color: var(--primary);
}
.customer-type-option input[type="radio"] { display: none; }
.business-check-label {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    cursor: pointer;
    font-size: 0.88rem;
    color: var(--gray-600);
    user-select: none;
}
.business-check-label:hover { color: var(--primary); }
.business-check-label.active { color: var(--primary); font-weight: 600; }
.business-check-label input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
    accent-color: var(--primary);
    cursor: pointer;
    margin: 0;
    vertical-align: middle;
}
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

document.getElementById('is_business').addEventListener('change', function() {
    const label = this.closest('.business-check-label');
    const businessGroup = document.getElementById('businessGroup');
    if (this.checked) {
        label.classList.add('active');
        businessGroup.style.display = '';
    } else {
        label.classList.remove('active');
        businessGroup.style.display = 'none';
    }
});
</script>
@endpush
