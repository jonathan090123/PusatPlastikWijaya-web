<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP - Pusat Plastik Wijaya</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .wrapper { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .header { background: #2563eb; padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .header p { color: #bfdbfe; margin: 6px 0 0; font-size: 13px; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .otp-box { background: #eff6ff; border: 2px dashed #93c5fd; border-radius: 10px; text-align: center; padding: 24px; margin: 24px 0; }
        .otp-code { font-size: 42px; font-weight: 700; letter-spacing: 10px; color: #1d4ed8; }
        .otp-note { font-size: 13px; color: #6b7280; margin-top: 8px; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Pusat Plastik Wijaya</h1>
        <p>{{ $purpose === 'reset' ? 'Reset Password' : 'Verifikasi Email' }}</p>
    </div>
    <div class="body">
        <p>Halo,</p>
        @if($purpose === 'reset')
            <p>Kami menerima permintaan reset password untuk akun Anda. Gunakan kode OTP berikut untuk melanjutkan:</p>
        @else
            <p>Terima kasih telah mendaftar di <strong>Pusat Plastik Wijaya</strong>. Gunakan kode OTP berikut untuk memverifikasi email Anda:</p>
        @endif

        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
            <div class="otp-note">Kode berlaku selama <strong>10 menit</strong></div>
        </div>

        <p>Jika Anda tidak melakukan permintaan ini, abaikan email ini.</p>
        <p>Salam,<br><strong>Tim Pusat Plastik Wijaya</strong></p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Pusat Plastik Wijaya. Jangan balas email ini.
    </div>
</div>
</body>
</html>
