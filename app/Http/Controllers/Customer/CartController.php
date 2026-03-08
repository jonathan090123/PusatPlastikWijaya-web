<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function getOrCreateCart()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        return $cart;
    }

    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cart->load('items.product.category');

        return view('customer.cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active) {
            return response()->json(['success' => false, 'message' => 'Produk tidak tersedia'], 400);
        }

        if ($product->stock < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi'], 400);
        }

        $cart = $this->getOrCreateCart();

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $request->quantity;
            if ($newQty > $product->stock) {
                return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi'], 400);
            }
            $cartItem->update(['quantity' => $newQty]);
        }
        else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
            ]);
        }

        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang!',
            'cart_count' => $cart->total_items,
        ]);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Verify ownership
        $cart = $this->getOrCreateCart();
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($request->quantity > $cartItem->product->stock) {
            return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi'], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);
        $cart->load('items.product');

        return response()->json([
            'success' => true,
            'subtotal' => $cartItem->subtotal,
            'total' => $cart->total,
            'cart_count' => $cart->total_items,
        ]);
    }

    public function remove(CartItem $cartItem)
    {
        $cart = $this->getOrCreateCart();
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $cartItem->delete();
        $cart->load('items.product');

        return response()->json([
            'success' => true,
            'message' => 'Item dihapus dari keranjang',
            'total' => $cart->total,
            'cart_count' => $cart->total_items,
        ]);
    }

    public function count()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        $count = $cart ? $cart->items->sum('quantity') : 0;

        return response()->json(['count' => $count]);
    }
}
