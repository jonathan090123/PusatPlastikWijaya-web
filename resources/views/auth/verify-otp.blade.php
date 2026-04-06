@extends('layouts.customer')

@section('title', 'Verifikasi Email - Pusat Plastik Wijaya')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><i class="fas fa-envelope-open-text"></i></h1>
            <h2>Verifikasi Email Anda</h2>
            <p>Kami telah mengirim kode OTP 6 digit ke email <strong>{{ session('otp_email') }}</strong>. Berlaku 10 menit.</p>
        </div>

        @if(session('success'))
        <div class="alert-success-register">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('info'))
        <div class="alert-info-box">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
        </div>
        @endif

        <form method="POST" action="{{ route('verify-email.store') }}" class="auth-form">
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

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-check-circle"></i> Verifikasi
            </button>
        </form>

        <div style="margin-top: 1.25rem; text-align: center;">
            <p style="font-size:0.85rem; color:var(--gray-500);">Tidak menerima kode?</p>
            <form method="POST" action="{{ route('verify-email.resend') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-link-resend">
                    <i class="fas fa-redo"></i> Kirim ulang OTP
                </button>
            </form>
        </div>

        <div class="auth-footer" style="margin-top:1.5rem;">
            <p><a href="{{ route('register') }}">← Kembali ke halaman daftar</a></p>
        </div>
    </div>
</div>

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
.alert-info-box {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    color: #1e40af;
    font-size: 0.88rem;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
}
.btn-link-resend {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    text-decoration: underline;
}
.btn-link-resend:hover { opacity: 0.75; }
</style>
@endpush
@endsection
