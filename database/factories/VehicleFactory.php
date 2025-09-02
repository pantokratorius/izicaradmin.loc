<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = \App\Models\Vehicle::class;

    public function definition(): array
    {
        return [
            'vin' => strtoupper($this->faker->unique()->bothify('??######??????###')),
            'vehicle_type' => $this->faker->randomElement(['Легковой', 'Грузовой', 'Мотоцикл']),
            'brand' => $this->faker->company(),
            'model' => $this->faker->word(),
            'generation' => $this->faker->optional()->word(),
            'body' => $this->faker->optional()->word(),
            'modification' => $this->faker->optional()->word(),
            'registration_number' => $this->faker->optional()->bothify('A###AA##'),
            'sts' => $this->faker->optional()->bothify('??######'),
            'pts' => $this->faker->optional()->bothify('??######'),
            'year_of_manufacture' => $this->faker->year(),
            'engine_type' => $this->faker->randomElement(['Бензин', 'Дизель', 'Гибрид', 'Электро']),
            'client_id' => Client::factory(),
        ];
    }
}
