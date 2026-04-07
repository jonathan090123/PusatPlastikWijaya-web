<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminBusinessVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer')
            ->where('customer_type', 'business')
            ->whereNotNull('business_verified');

        if ($request->filled('status')) {
            $query->where('business_verified', $request->status);
        } else {
            // Default: tampilkan pending duluan
            $query->orderByRaw("FIELD(business_verified, 'pending', 'approved', 'rejected')");
        }

        $query->latest();
        $customers = $query->paginate(15)->withQueryString();

        // Daftar nama bisnis yang sudah approved — untuk deteksi duplikat
        $approvedBusinessNames = User::where('role', 'customer')
            ->where('customer_type', 'business')
            ->where('business_verified', 'approved')
            ->whereNotNull('business_name')
            ->pluck('business_name')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();

        return view('admin.business-verification.index', compact('customers', 'approvedBusinessNames'));
    }

    public function approve(User $customer)
    {
        abort_if($customer->customer_type !== 'business', 403);

        $customer->update(['business_verified' => 'approved']);

        return redirect()->back()->with('success', "Akun bisnis {$customer->business_name} ({$customer->name}) berhasil diverifikasi.");
    }

    public function reject(Request $request, User $customer)
    {
        abort_if($customer->customer_type !== 'business', 403);

        $customer->update(['business_verified' => 'rejected']);

        return redirect()->back()->with('success', "Akun bisnis {$customer->business_name} ({$customer->name}) telah ditolak.");
    }
}
