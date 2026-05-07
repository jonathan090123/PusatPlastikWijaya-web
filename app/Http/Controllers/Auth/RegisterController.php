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
                // Tolak jika email sudah ada di DB
                $existsInDb = \App\Models\User::where('email', $value)->exists();
                if ($existsInDb) {
                    $fail('Email sudah terdaftar.');
                    return;
                }
                // Tolak jika email sedang pending OTP
                $pendingKey = 'pending_reg_' . md5($value);
                if (Cache::has($pendingKey)) {
                    $fail('Email ini sedang dalam proses pendaftaran. Silakan cek email Anda atau tunggu beberapa menit.');
                }
            }],
            'phone'         => 'required|string|max:20',
            'city_type'     => 'required|in:blitar,outside',
            'address'       => 'required|string|max:1000',
            'customer_type' => 'nullable|in:personal,business',
            'business_name' => [
                'nullable', 'string', 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request->boolean('is_business') || blank($value)) {
                        return;
                    }
                    // Normalisasi nama bisnis
                    $normalize = fn($s) => preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9\s]/u', '', strtolower(trim($s))));
                    $normalizedName  = $normalize($value);

                    // Cek nama bisnis di DB
                    $existsInDb = \App\Models\User::where('customer_type', 'business')
                        ->whereIn('business_verified', ['pending', 'approved'])
                        ->whereNotNull('business_name')
                        ->get(['business_name'])
                        ->contains(fn($u) => $normalize($u->business_name) === $normalizedName);

                    // Cek nama bisnis di cache
                    $existsInCache = Cache::has('pending_biz_' . md5($normalizedName));

                    if ($existsInDb || $existsInCache) {
                        $fail('Nama bisnis ini sudah terdaftar. Jika Anda berhak mendaftar atas nama bisnis ini, hubungi admin 082313505557.');
                    }
                },
            ],
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

        $isBusiness    = $request->boolean('is_business');
        $businessName  = $isBusiness ? $request->business_name : null;

        // Simpan data pendaftaran sementara di cache
        $pendingKey = 'pending_reg_' . md5($request->email);
        Cache::put($pendingKey, [
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'city_type'     => $request->city_type,
            'address'       => $request->address,
            'customer_type' => $isBusiness ? 'business' : 'personal',
            'business_name' => $businessName,
            'password'      => Hash::make($request->password),
        ], now()->addMinutes(15));

        // Tandai nama bisnis pending di cache
        if ($isBusiness && filled($businessName)) {
            $normalizedForCache = preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9\s]/u', '', strtolower(trim($businessName))));
            Cache::put('pending_biz_' . md5($normalizedForCache), true, now()->addMinutes(15));
        }

        $otp      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpKey   = 'email_otp_' . md5($request->email);
        Cache::put($otpKey, $otp, now()->addMinutes(10));

        Mail::to($request->email)->send(new OtpMail($otp, 'verify'));

        $request->session()->put('otp_email', $request->email);

        return redirect()->route('verify-email')
            ->with('success', 'Kode OTP telah dikirim ke email Anda. Berlaku 10 menit.');
    }
}
