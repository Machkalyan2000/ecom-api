<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function pay(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $cart = $user->cart()->with('products')->first();

        if (!$cart || $cart->products->isEmpty()) {
            return response()->json(['message' => 'Корзина пуста'], 400);
        }

        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        $total = $cart->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });

        $order = DB::transaction(function () use ($user, $cart, $paymentMethod, $total) {
            $order = Order::create([
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethod->id,
                'total_price' => $total,
                'status' => 'pending',
            ]);

            $order->payment_link = str_replace('{order_id}', $order->id, $paymentMethod->payment_url_template);
            $order->save();

            $cart->products()->detach();
            $cart->delete();

            return $order;
        });

        return response()->json([
            'order_id' => $order->id,
            'payment_link' => $order->payment_link,
        ]);
    }

    public function markPaid(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Статус уже обновлен'], 400);
        }

        $order->status = 'paid';
        $order->save();

        return response()->json(['message' => 'Заказ оплачен']);
    }

    public function list(Request $request)
    {
        $query = Auth::user()->orders()->with('paymentMethod');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($request->has('sort_date')) {
            $direction = $request->get('sort_date') === 'desc' ? 'desc' : 'asc';
            $orders = $query->orderBy('created_at', $direction);
        } else {
            $orders = $query->orderBy('created_at', 'desc');
        }

        return response()->json($orders->get());
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Нет доступа'], 403);
        }

        return response()->json($order->load('paymentMethod'));
    }
}
