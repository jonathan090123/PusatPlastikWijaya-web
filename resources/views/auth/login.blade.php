@extends('layouts.customer')

@section('title', 'Login - Pusat Plastik Wijaya')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><i class="fas fa-sign-in-alt"></i></h1>
            <h2>Masuk ke Akun Anda</h2>
            <p>Selamat datang kembali! Silakan masuk dengan akun Anda.</p>
        </div>

        @if(session('success'))
        <div class="alert-success-register">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="nama@email.com" required autofocus
                       class="@error('email') is-invalid @enderror">
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <div class="password-input-group">
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password" required
                           class="@error('password') is-invalid @enderror">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group form-check">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <div class="auth-footer">
            <p>Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
        </div>

        <div style="margin-top: 1rem; padding: 0.75rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 0.75rem; color: #1e40af;">
            <strong><i class="fas fa-info-circle"></i> Demo Admin:</strong><br>
            Email: <code>admin@plastikwijaya.com</code><br>
            Password: <code>admin123</code>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling?.querySelector('i') || input.parentElement.querySelector('.password-toggle i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

@push('styles')
<style>
.alert-success-register {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: var(--radius, 8px);
    color: #166534;
    font-size: 0.88rem;
    font-weight: 600;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
}
.alert-success-register i {
    font-size: 1.1rem;
    color: #16a34a;
    flex-shrink: 0;
}
</style>
@endpush
@endsection
