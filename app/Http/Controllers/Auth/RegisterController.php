<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'string', 'email', 'max:255', function ($attribute, $value, $fail) {
                $existing = \App\Models\User::where('email', $value)->whereNotNull('email_verified_at')->first();
                if ($existing) {
                    $fail('Email sudah terdaftar.');
                }
            }],
            'phone'         => 'required|string|max:20',
            'city_type'     => 'required|in:blitar,outside',
            'address'       => 'required|string|max:1000',
            'customer_type' => 'nullable|in:personal,business',
            'business_name' => 'nullable|string|max:255',
            'password'      => 'required|string|min:8|confirmed',
        ], [
            'name.required'             => 'Nama wajib diisi.',
            'email.required'            => 'Email wajib diisi.',
            'phone.required'            => 'Nomor telepon wajib diisi.',
            'city_type.required'        => 'Pilih lokasi kota Anda.',
            'address.required'          => 'Alamat lengkap wajib diisi.',
            'business_name.required_if' => 'Nama usaha wajib diisi untuk pelanggan bisnis.',
            'password.required'         => 'Password wajib diisi.',
            'password.min'              => 'Password minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
        ]);

        // Simpan data pendaftaran sementara di cache — belum masuk DB
        $pendingKey = 'pending_reg_' . md5($request->email);
        Cache::put($pendingKey, [
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'city_type'     => $request->city_type,
            'address'       => $request->address,
            'customer_type' => $request->boolean('is_business') ? 'business' : 'personal',
            'business_name' => $request->boolean('is_business') ? $request->business_name : null,
            'password'      => Hash::make($request->password),
        ], now()->addMinutes(15));

        $otp      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpKey   = 'email_otp_' . md5($request->email);
        Cache::put($otpKey, $otp, now()->addMinutes(10));

        Mail::to($request->email)->send(new OtpMail($otp, 'verify'));

        $request->session()->put('otp_email', $request->email);

        return redirect()->route('verify-email')
            ->with('success', 'Kode OTP telah dikirim ke email Anda. Berlaku 10 menit.');
    }
}
