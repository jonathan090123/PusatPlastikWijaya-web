<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CustomerPointController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $histories = $user->pointHistories()
            ->with('order')
            ->latest()
            ->paginate(15);

        return view('customer.points.index', compact('user', 'histories'));
    }
}
