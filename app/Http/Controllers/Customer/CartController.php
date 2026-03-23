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
        $cart->load('items.product.category', 'items.product.productUnits');

        return view('customer.cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'unit'       => 'nullable|string|max:20',
        ]);

        $product = Product::with('productUnits')->findOrFail($request->product_id);

        if (!$product->is_active) {
            return response()->json(['success' => false, 'message' => 'Produk tidak tersedia'], 400);
        }

        // Resolve unit and its conversion factor (how many base units per 1 selected unit)
        $selectedUnit = $request->unit ?: $product->unit;
        $conversion   = 1;
        if ($selectedUnit !== $product->unit) {
            $pu = $product->productUnits->firstWhere('unit', $selectedUnit);
            if (!$pu) {
                return response()->json(['success' => false, 'message' => 'Satuan tidak valid'], 400);
            }
            $conversion = (int) $pu->conversion_value;
        }

        // Stock check in base units
        $requiredStock = $request->quantity * $conversion;
        if ($product->stock < $requiredStock) {
            return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi'], 400);
        }

        $cart = $this->getOrCreateCart();

        // Cart items are keyed by product+unit combination
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('unit', $selectedUnit)
            ->first();

        if ($cartItem) {
            $newQty        = $cartItem->quantity + $request->quantity;
            $newStockNeeded = $newQty * $conversion;
            if ($newStockNeeded > $product->stock) {
                return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi'], 400);
            }
            $cartItem->update(['quantity' => $newQty]);
        } else {
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
                'unit'       => $selectedUnit,
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

        // Stock check with conversion factor
        $cartItem->load('product.productUnits');
        $unitName   = $cartItem->unit ?: $cartItem->product->unit;
        $conversion = 1;
        if ($unitName !== $cartItem->product->unit) {
            $pu = $cartItem->product->productUnits->firstWhere('unit', $unitName);
            if ($pu) $conversion = (int) $pu->conversion_value;
        }
        if (($request->quantity * $conversion) > $cartItem->product->stock) {
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
