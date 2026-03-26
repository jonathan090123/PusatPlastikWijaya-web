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

        // Search — includes product_code as hidden keyword (not shown in UI)
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                  ->orWhere('product_code', 'like', '%' . $term . '%');
            });
        }

        // Filter by category slug
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Produk promo selalu muncul lebih dahulu (berlaku di semua sort)
        $query->orderByRaw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN 0 ELSE 1 END ASC');

        // Sorting sekunder sesuai pilihan user
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

    public function suggest(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $products = Product::with('category')
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                      ->orWhere('product_code', 'like', '%' . $q . '%');
            })
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$q . '%'])
            ->limit(8)
            ->get(['id', 'name', 'slug', 'image', 'price', 'discount_price', 'category_id']);

        return response()->json($products->map(function ($p) {
            $isPromo = $p->discount_price && $p->discount_price < $p->price;
            return [
                'name'           => $p->name,
                'slug'           => $p->slug,
                'category'       => $p->category->name ?? '',
                'image'          => $p->image ? asset('storage/' . $p->image) : null,
                'price'          => 'Rp ' . number_format($isPromo ? $p->discount_price : $p->price, 0, ',', '.'),
                'price_original' => $isPromo ? 'Rp ' . number_format($p->price, 0, ',', '.') : null,
                'is_promo'       => $isPromo,
            ];
        }));
    }

    public function show($slug)
    {
        $product = Product::with(['category', 'productUnits'])
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
