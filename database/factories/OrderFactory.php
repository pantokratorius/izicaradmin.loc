<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\User;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        // Randomly decide if this order is for a vehicle or only a client
        $hasVehicle = $this->faker->boolean(70); // 70% of orders linked to vehicles

        return [
            'order_number' => $this->faker->unique()->numerify('ORD###'),
            'amount'       => $this->faker->randomFloat(2, 1000, 100000),
            'created_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'vehicle_id'   => $hasVehicle ? Vehicle::inRandomOrder()->first()?->id : null,
            'client_id'    => $hasVehicle
                             ? Vehicle::inRandomOrder()->first()?->client_id // vehicle owner
                             : Client::inRandomOrder()->first()?->id,
            'manager_id'   => User::inRandomOrder()->first()?->id,
            'mileage'      => $this->faker->numberBetween(0, 300000),
        ];
    }
}
