<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.profile', compact('user'));
        }

        return view('customer.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
        ];

        if (!$user->isAdmin()) {
            $rules['city_type']     = 'required|in:blitar,outside';
            $rules['address']       = 'required|string|max:1000';
            $rules['customer_type'] = 'nullable|in:personal,business';
            $rules['business_name'] = 'nullable|string|max:255';
        } else {
            $rules['address'] = 'nullable|string|max:1000';
        }

        $validated = $request->validate($rules);

        // Default to personal if not submitted, clear business fields if personal
        if (!$user->isAdmin()) {
            $validated['customer_type'] = $request->boolean('is_business') ? 'business' : 'personal';
            if ($validated['customer_type'] === 'personal') {
                $validated['business_name'] = null;
            }
        }

        $user->update($validated);

        if ($user->isAdmin()) {
            return back()->with('success', 'Profil berhasil diperbarui!');
        }

        return redirect()->route('home')->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        if ($user->isAdmin()) {
            return back()->with('success', 'Password berhasil diperbarui!');
        }

        return redirect()->route('home')->with('success', 'Password berhasil diperbarui!');
    }
}
