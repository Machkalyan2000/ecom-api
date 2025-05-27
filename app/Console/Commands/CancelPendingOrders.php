<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CancelPendingOrders extends Command
{
    protected $signature = 'orders:cancel-pending';
    protected $description = 'Отменяет заказы, не оплаченные в течение 2 минут';

    public function handle()
    {
        $cutoff = Carbon::now()->subMinutes(2);

        $orders = Order::where('status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->get();

        foreach ($orders as $order) {
            $order->status = 'cancelled';
            $order->save();
            $this->info("Отменен заказ ID: {$order->id}");
        }

        return 0;
    }
}
