<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class VerifyEmailOtpController extends Controller
{
    public function show(Request $request)
    {
        if (!$request->session()->has('otp_email')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits'   => 'Kode OTP harus 6 digit.',
        ]);

        $email = $request->session()->get('otp_email');

        if (!$email) {
            return redirect()->route('register')->with('error', 'Sesi habis. Silakan daftar ulang.');
        }

        $otpKey     = 'email_otp_' . md5($email);
        $pendingKey = 'pending_reg_' . md5($email);
        $storedOtp  = Cache::get($otpKey);

        // OTP sudah expired (cache habis)
        if (!$storedOtp) {
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang.']);
        }

        // OTP salah
        if ($request->otp !== $storedOtp) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        // OTP benar — ambil data pendaftaran dari cache, baru buat akun
        $pending = Cache::get($pendingKey);

        if (!$pending) {
            // Data pendaftaran expired (>15 menit), minta daftar ulang
            Cache::forget($otpKey);
            $request->session()->forget('otp_email');
            return redirect()->route('register')
                ->with('error', 'Sesi pendaftaran habis. Silakan daftar ulang.');
        }

        $isBusiness = $pending['customer_type'] === 'business';

        $user = User::create([
            'name'              => $pending['name'],
            'email'             => $pending['email'],
            'phone'             => $pending['phone'],
            'city_type'         => $pending['city_type'],
            'address'           => $pending['address'],
            'customer_type'     => $pending['customer_type'],
            'business_name'     => $pending['business_name'],
            'business_verified' => $isBusiness ? 'pending' : null,
            'password'          => $pending['password'],
            'role'              => 'customer',
            'email_verified_at' => now(),
        ]);

        Cache::forget($otpKey);
        Cache::forget($pendingKey);

        // Hapus kunci nama bisnis yang sedang pending — data sudah masuk DB
        if ($user->customer_type === 'business' && filled($user->business_name)) {
            Cache::forget('pending_biz_' . md5(strtolower(trim($user->business_name))));
        }

        $request->session()->forget('otp_email');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Registrasi berhasil! Selamat datang, ' . $user->name . '!');
    }

    public function resend(Request $request)
    {
        $email = $request->session()->get('otp_email');

        if (!$email) {
            return redirect()->route('register');
        }

        // Cek data pendaftaran masih ada
        $pendingKey = 'pending_reg_' . md5($email);
        if (!Cache::has($pendingKey)) {
            $request->session()->forget('otp_email');
            return redirect()->route('register')
                ->with('error', 'Sesi pendaftaran habis. Silakan daftar ulang.');
        }

        $otp    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpKey = 'email_otp_' . md5($email);
        Cache::put($otpKey, $otp, now()->addMinutes(10));

        Mail::to($email)->send(new OtpMail($otp, 'verify'));

        return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda. Berlaku 10 menit.');
    }
}
