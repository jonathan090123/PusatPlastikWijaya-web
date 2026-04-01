<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            'email'         => 'required|string|email|max:255|unique:users',
            'phone'         => 'required|string|max:20',
            'city_type'     => 'required|in:blitar,outside',
            'address'       => 'required|string|max:1000',
            'customer_type' => 'nullable|in:personal,business',
            'business_name' => 'nullable|string|max:255',
            'password'      => 'required|string|min:8|confirmed',
        ], [
            'name.required'          => 'Nama wajib diisi.',
            'email.required'         => 'Email wajib diisi.',
            'email.unique'           => 'Email sudah terdaftar.',
            'phone.required'         => 'Nomor telepon wajib diisi.',
            'city_type.required'     => 'Pilih lokasi kota Anda.',
            'address.required'       => 'Alamat lengkap wajib diisi.',
            'business_name.required_if' => 'Nama usaha wajib diisi untuk pelanggan bisnis.',
            'password.required'      => 'Password wajib diisi.',
            'password.min'           => 'Password minimal 8 karakter.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'city_type'     => $request->city_type,
            'address'       => $request->address,
            'customer_type' => $request->boolean('is_business') ? 'business' : 'personal',
            'business_name' => $request->boolean('is_business') ? $request->business_name : null,
            'password'      => Hash::make($request->password),
            'role'          => 'customer',
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }
}
