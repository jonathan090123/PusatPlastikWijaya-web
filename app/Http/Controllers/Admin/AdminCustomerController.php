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
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn($q) => $q->whereIn('status', ['paid','processing','shipped','completed'])], 'total');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter: has_orders
        if ($request->filter === 'has_orders') {
            $query->has('orders');
        } elseif ($request->filter === 'no_orders') {
            $query->doesntHave('orders');
        } elseif ($request->filter === 'has_points') {
            $query->where('points', '>', 0);
        }

        // Sort
        match ($request->sort) {
            'points_desc'  => $query->orderByDesc('points'),
            'orders_desc'  => $query->orderByDesc('orders_count'),
            'spent_desc'   => $query->orderByDesc('total_spent'),
            'oldest'       => $query->oldest(),
            default        => $query->latest(),
        };

        $customers = $query->paginate(15)->withQueryString();

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

    public function toggleActive(User $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Akun {$customer->name} berhasil {$status}.");
    }
}
