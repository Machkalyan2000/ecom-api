<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CartController extends Controller
{
    public function index()
    {
        $cart = Auth::user()->cart()->with('products')->first();
        return response()->json($cart);
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart()->firstOrCreate([]);

        $productId = $request->product_id;
        $quantity = $request->quantity ?? 1;

        if ($cart->products()->where('product_id', $productId)->exists()) {
            $cart->products()->updateExistingPivot($productId, [
                'quantity' => DB::raw('quantity + ' . (int) $quantity),
            ]);
        } else {
            $cart->products()->attach($productId, ['quantity' => $quantity]);
        }

        return response()->json(['message' => 'Товар добавлен в корзину']);
    }

    public function removeProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $cart = $user?->cart;

        if (!$cart) {
            return response()->json(['message' => 'Корзина пуста'], 404);
        }

        $cart->products()->detach($request->product_id);

        return response()->json(['message' => 'Товар удален из корзины']);
    }
}
