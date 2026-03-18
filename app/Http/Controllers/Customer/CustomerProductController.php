<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class CustomerProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category slug
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'harga-rendah':
                $query->orderByRaw('COALESCE(discount_price, price) ASC');
                break;
            case 'harga-tinggi':
                $query->orderByRaw('COALESCE(discount_price, price) DESC');
                break;
            case 'nama':
                $query->orderBy('name');
                break;
            default: // terbaru
                $query->latest();
                break;
        }

        $products = $query->paginate(48)->withQueryString();
        $categories = Category::where('is_active', true)->withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])->orderBy('name')->get();

        return view('customer.products.index', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedProducts = Product::with('category')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('customer.products.show', compact('product', 'relatedProducts'));
    }
}
