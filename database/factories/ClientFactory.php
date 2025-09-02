<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = \App\Models\Client::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->optional()->lastName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'segment' => $this->faker->randomElement(['Розница', 'Сотрудники', 'СТО']),
            'discount' => $this->faker->randomFloat(2, 0, 30), // 0–30%
        ];
    }

    // Add this new state to create vehicles automatically
    public function withVehicles($count = 1)
    {
        return $this->afterCreating(function ($client) use ($count) {
            \App\Models\Vehicle::factory()->count($count)->create([
                'client_id' => $client->id,
            ]);
        });
    }
}
