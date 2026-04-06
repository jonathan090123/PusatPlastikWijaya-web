@extends('layouts.customer')

@section('title', 'Reset Password - Pusat Plastik Wijaya')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><i class="fas fa-lock"></i></h1>
            <h2>Reset Password</h2>
            <p>Masukkan kode OTP yang dikirim ke <strong>{{ session('reset_email') }}</strong> dan buat password baru.</p>
        </div>

        @if(session('success'))
        <div class="alert-success-register">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="otp"><i class="fas fa-key"></i> Kode OTP</label>
                <input type="text" id="otp" name="otp"
                       placeholder="_ _ _ _ _ _"
                       maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                       autofocus required
                       class="otp-input @error('otp') is-invalid @enderror">
                @error('otp')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password Baru</label>
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
                           placeholder="Ulangi password baru" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-save"></i> Simpan Password Baru
            </button>
        </form>

        <div class="auth-footer" style="margin-top:1.5rem;">
            <p><a href="{{ route('password.request') }}">← Kembali</a></p>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling?.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon?.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon?.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

@push('styles')
<style>
.otp-input {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: 0.5rem;
    padding: 0.75rem;
}
.alert-success-register {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 8px;
    color: #166534;
    font-size: 0.88rem;
    font-weight: 600;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
}
.alert-success-register i { font-size: 1.1rem; color: #16a34a; flex-shrink: 0; }
</style>
@endpush
@endsection
