<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\OrderItem;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(), // создаём заказ, если не передан
            'part_number' => $this->faker->numberBetween(1000, 1000000),
            'part_make' => $this->faker->word . ' ' . $this->faker->randomElement(['Kayaba', 'Tp', 'Denso', 'Koyo']),
            'part_name' => $this->faker->word . ' ' . $this->faker->randomElement(['Filter', 'Brake', 'Spark Plug', 'Battery']),
            'sale_price' => $this->faker->randomFloat(2, 100, 5000),
            'purchase_price' => $this->faker->randomFloat(2, 50, 4000),
            'supplier' => $this->faker->company,
            'prepayment' => $this->faker->randomFloat(2, 0, 1000),
            'quantity' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement([1,2,3]),
            'created_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'comment' => $this->faker->optional()->sentence,
        ];
    }
}
