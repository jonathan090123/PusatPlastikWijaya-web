@extends('layouts.app')

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
                <label for="name"><i class="fas fa-user"></i> Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       placeholder="Masukkan nama lengkap" required autofocus
                       class="@error('name') is-invalid @enderror">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="nama@email.com" required
                       class="@error('email') is-invalid @enderror">
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Nomor Telepon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       placeholder="08xxxxxxxxxx" required
                       class="@error('phone') is-invalid @enderror">
                @error('phone')
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
</script>
@endsection
