<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer')
            ->withCount('orders');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        $customer->loadCount('orders');
        $customer->load([
            'orders' => fn($q) => $q->latest()->take(10),
            'orders.items',
            'pointHistories' => fn($q) => $q->latest()->take(10),
        ]);

        $totalSpent = $customer->orders()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
            ->sum('total');

        return view('admin.customers.show', compact('customer', 'totalSpent'));
    }
}
