@extends('layouts.customer')

@section('title', 'Lupa Password - Pusat Plastik Wijaya')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><i class="fas fa-unlock-alt"></i></h1>
            <h2>Lupa Password?</h2>
            <p>Masukkan email terdaftar Anda. Kami akan mengirimkan kode OTP untuk reset password.</p>
        </div>

        @if(session('error'))
        <div class="alert-error-box">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
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

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane"></i> Kirim Kode OTP
            </button>
        </form>

        <div class="auth-footer" style="margin-top:1.5rem;">
            <p>Ingat password? <a href="{{ route('login') }}">Masuk di sini</a></p>
        </div>
    </div>
</div>

@push('styles')
<style>
.alert-error-box {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-radius: 8px;
    color: #991b1b;
    font-size: 0.88rem;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
}
</style>
@endpush
@endsection
