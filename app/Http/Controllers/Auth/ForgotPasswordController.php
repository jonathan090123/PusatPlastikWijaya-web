<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.exists'   => 'Email tidak ditemukan. Pastikan email sudah terdaftar.',
        ]);

        $otp      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'reset_otp_' . md5($request->email);
        Cache::put($cacheKey, $otp, now()->addMinutes(10));

        Mail::to($request->email)->send(new OtpMail($otp, 'reset'));

        $request->session()->put('reset_email', $request->email);

        return redirect()->route('password.reset.form')
            ->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function showResetForm(Request $request)
    {
        if (!$request->session()->has('reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'otp'                   => 'required|digits:6',
            'password'              => 'required|string|min:8|confirmed',
        ], [
            'otp.required'          => 'Kode OTP wajib diisi.',
            'otp.digits'            => 'Kode OTP harus 6 digit.',
            'password.required'     => 'Password baru wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        $email = $request->session()->get('reset_email');

        if (!$email) {
            return redirect()->route('password.request')->with('error', 'Sesi habis. Silakan ulangi.');
        }

        $cacheKey = 'reset_otp_' . md5($email);
        $storedOtp = Cache::get($cacheKey);

        if (!$storedOtp || $request->otp !== $storedOtp) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kedaluwarsa.']);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request');
        }

        $user->update([
            'password'          => Hash::make($request->password),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        Cache::forget($cacheKey);
        $request->session()->forget('reset_email');

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login.');
    }
}
