<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderItem;
use App\Models\Order;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        // Берём существующие заказы или создаём новые
        $orders = Order::all();

        if ($orders->isEmpty()) {
            $orders = Order::factory(5)->create(); // создаём 5 заказов
        }

        foreach ($orders as $order) {
            // Для каждого заказа создаём 2-5 позиций
            OrderItem::factory(rand(2, 5))->create([
                'order_id' => $order->id
            ]);
        }
    }
}
