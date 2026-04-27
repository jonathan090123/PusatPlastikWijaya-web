@extends('layouts.customer')

@section('title', 'Daftar - Pusat Plastik Wijaya')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="position: relative;">
        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm back-link-guest">
            <i class="fas fa-arrow-left"></i> <span class="hide-on-mobile">Kembali</span>
        </a>
        <div class="auth-header">
            <h1><i class="fas fa-user-plus"></i></h1>
            <h2>Buat Akun Baru</h2>
            <p>Daftar untuk mulai berbelanja di Pusat Plastik Wijaya.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Nama Lengkap <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       placeholder="Masukkan nama lengkap" required autofocus
                       class="@error('name') is-invalid @enderror">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email <span style="color:var(--danger);">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="nama@email.com" required
                       class="@error('email') is-invalid @enderror">
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Nomor Telepon <span style="color:var(--danger);">*</span></label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       placeholder="08xxxxxxxxxx" required
                       class="@error('phone') is-invalid @enderror">
                @error('phone')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Lokasi Kota <span style="color:var(--danger);">*</span></label>
                <div style="display:flex; gap:0.75rem; margin-top:0.25rem;">
                    <label class="city-option {{ old('city_type') === 'blitar' ? 'selected' : '' }}">
                        <input type="radio" name="city_type" value="blitar" {{ old('city_type') === 'blitar' ? 'checked' : '' }} required>
                        <i class="fas fa-city"></i>
                        <span>Kota Blitar</span>
                    </label>
                    <label class="city-option {{ old('city_type') === 'outside' ? 'selected' : '' }}">
                        <input type="radio" name="city_type" value="outside" {{ old('city_type') === 'outside' ? 'checked' : '' }}>
                        <i class="fas fa-globe-asia"></i>
                        <span>Luar Kota Blitar</span>
                    </label>
                </div>
                @error('city_type')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group" id="addressGroup" style="{{ old('city_type') ? '' : 'display:none;' }}">
                <label for="address"><i class="fas fa-home"></i> Alamat Lengkap <span style="color:var(--danger);">*</span></label>
                <textarea id="address" name="address" rows="3"
                          placeholder="{{ old('city_type') === 'outside' ? 'Contoh: Surabaya, Jl. Ahmad Yani No. 5, RT 01/RW 02, Kel. Wonokromo' : 'Contoh: Jl. Bali No. 20, RT 03/RW 05, Kel. Sananwetan' }}"
                          required class="@error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                @error('address')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Tipe Pelanggan --}}
            <div class="form-group">
                <p style="font-size:0.75rem; color:var(--gray-400); margin-bottom:0.4rem; margin-top:-0.25rem;"><b>(Opsional)</b> - lewati jika Anda bukan dari usaha/perusahaan</p>
                <label class="business-check-label {{ old('is_business') ? 'active' : '' }}">
                    <input type="checkbox" id="is_business" name="is_business" value="1"
                           {{ old('is_business') ? 'checked' : '' }}>

                    <span>Saya mewakili usaha / perusahaan</span>
                </label>
            </div>

            {{-- Info Bisnis (muncul jika checkbox dicentang) --}}
            <div id="businessGroup" style="{{ old('is_business') ? '' : 'display:none;' }}">
                <div class="form-group">
                    <label for="business_name"><i class="fas fa-building"></i> Nama Usaha / Perusahaan</label>
                    <input type="text" id="business_name" name="business_name"
                           value="{{ old('business_name') }}"
                           placeholder="Contoh: CV Maju Bersama / Toko Sari Plastik"
                           class="@error('business_name') is-invalid @enderror">
                    @error('business_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <div class="password-input-group">
                    <input type="password" id="password" name="password"
                           placeholder="Minimal 8 karakter" required
                           class="@error('password') is-invalid @enderror">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation"><i class="fas fa-lock"></i> Konfirmasi Password</label>
                <div class="password-input-group">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Ulangi password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Daftar
            </button>
        </form>

        <div class="auth-footer">
            <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
        </div>
    </div>
</div>

<style>
.city-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 600;
    font-size: 0.9rem;
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
    padding: 0.75rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 600;
    font-size: 0.9rem;
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
.back-link-guest {
    position: absolute;
    top: 1.5rem;
    left: calc(100% + 1rem);
    z-index: 10;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    text-decoration: none;
}
@media (max-width: 768px) {
    .back-link-guest {
        left: auto;
        right: 0;
        top: -2.5rem;
    }
    .auth-card {
        margin-top: 1rem;
    }
}
@media (max-width: 480px) {
    .back-link-guest {
        padding: 0.4rem 0.6rem;
    }
    .back-link-guest .hide-on-mobile {
        display: none;
    }
}
.back-link-guest:hover {
    color: var(--primary);
}
</style>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.parentElement.querySelector('.password-toggle i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.querySelectorAll('input[name="city_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.city-option').forEach(function(opt) { opt.classList.remove('selected'); });
        this.closest('.city-option').classList.add('selected');
        document.getElementById('addressGroup').style.display = '';
        var addressEl = document.getElementById('address');
        if (this.value === 'outside') {
            addressEl.placeholder = 'Contoh: Surabaya, Jl. Ahmad Yani No. 5, RT 01/RW 02, Kel. Wonokromo';
        } else {
            addressEl.placeholder = 'Contoh: Jl. Bali No. 20, RT 03/RW 05, Kel. Sananwetan';
        }
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

// Auto-trigger wwAlert jika ada error business_name
(function() {
    @error('business_name')
        wwAlert('Nama Bisnis Sudah Terdaftar', '{{ addslashes($message) }}');
    @enderror
})();
</script>

{{-- wwAlert Modal (same design as wwConfirm) --}}
<div id="wwAlertModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:99999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:12px; padding:1.75rem 1.5rem 1.5rem; max-width:340px; width:100%; text-align:center; box-shadow:0 16px 40px rgba(0,0,0,0.14); animation:wwAlertPop 0.22s cubic-bezier(0.34,1.56,0.64,1);">
        <div style="width:52px; height:52px; border-radius:50%; background:#fef3c7; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
            <i class="fas fa-exclamation-triangle" style="color:#d97706; font-size:1.2rem;"></i>
        </div>
        <h3 id="wwAlertTitle" style="font-size:1rem; font-weight:800; color:#111827; margin-bottom:0.4rem;"></h3>
        <p id="wwAlertMsg" style="font-size:0.83rem; color:#6b7280; line-height:1.6; margin-bottom:1.35rem;"></p>
        <button id="wwAlertOk" style="width:100%; padding:0.65rem; border-radius:8px; border:none; background:#2563eb; color:#fff; font-weight:700; font-size:0.875rem; cursor:pointer;">OK, Mengerti</button>
    </div>
</div>
<style>
@keyframes wwAlertPop { from{opacity:0;transform:scale(0.9)} to{opacity:1;transform:scale(1)} }
</style>
<script>
(function(){
    var modal = document.getElementById('wwAlertModal');
    var btnOk = document.getElementById('wwAlertOk');
    function closeAlert(){ modal.style.display = 'none'; }
    btnOk.addEventListener('click', closeAlert);
    modal.addEventListener('click', function(e){ if(e.target === modal) closeAlert(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeAlert(); });
    window.wwAlert = function(title, msg) {
        document.getElementById('wwAlertTitle').textContent = title;
        document.getElementById('wwAlertMsg').textContent   = msg;
        modal.style.display = 'flex';
    };
})();
</script>
@endsection
