@extends('layouts.customer')

@section('title', 'Daftar - Pusat Plastik Wijaya')

@section('content')
<div class="auth-container">
    <div class="auth-card">
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
                          placeholder="Contoh: Jl. Bali No. 20, RT 03/RW 05, Kel. Sananwetan"
                          required class="@error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                @error('address')
                    <span class="error-message">{{ $message }}</span>
                @enderror
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
    });
});
</script>
@endsection
