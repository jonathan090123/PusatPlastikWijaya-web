@extends('layouts.customer')

@section('title', 'Login - Pusat Plastik Wijaya')

@section('content')
    <div class="auth-container">
        <div class="auth-card" style="position: relative;">
            <a href="{{ route('home') }}" class="btn btn-secondary btn-sm back-link-guest">
                <i class="fas fa-arrow-left"></i> <span class="hide-on-mobile">Kembali</span>
            </a>
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
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
                        required autofocus class="@error('email') is-invalid @enderror">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="password-input-group">
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required
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
                    <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
            </form>

            <div class="auth-footer">
                <p>Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
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

                .form-check {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .forgot-link {
                    font-size: 0.85rem;
                    color: var(--primary);
                    text-decoration: none;
                }

                .forgot-link:hover {
                    text-decoration: underline;
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
                }

                @media (max-width: 480px) {
                    .back-link-guest {
                        padding: 0.4rem 0.6rem;
                    }
                    .back-link-guest .hide-on-mobile {
                        display: none;
                    }
                }
            </style>
        @endpush
@endsection