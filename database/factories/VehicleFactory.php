<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarGeneration;
use App\Models\CarSerie;
use App\Models\CarModification;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        // Pick random brand
         $brand = CarBrand::whereHas('models')->inRandomOrder()->first();
        if (!$brand) {
            throw new \Exception('No CarBrand found in database');
        }

        // Pick a model from this brand
        $model = $brand->models()->inRandomOrder()->first();
        if (!$model) {
            return [
                'car_brand_id' => $brand->id,
                'car_model_id' => $model->id,
                'car_generation_id' => null,
                'car_serie_id' => null,
                'car_modification_id' => null,
                'vin' => strtoupper($this->faker->bothify('??###########??')),
                'year_of_manufacture' => $this->faker->numberBetween(1995, now()->year),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Pick a generation (may not exist)
        $generation = CarGeneration::where('car_model_id', $model->id)->inRandomOrder()->first();

        // Pick a series (may not exist)
        $serie = CarSerie::where('car_model_id', $model->id)->inRandomOrder()->first();

        // Pick a modification (may not exist)
        $modification = CarModification::where('car_model_id', $model->id)->inRandomOrder()->first();



        return [
            'car_brand_id' => $brand->id,
            'car_model_id' => $model->id,
            'car_generation_id' => $generation?->id,
            'car_serie_id' => $serie?->id,
            'car_modification_id' => $modification?->id,
            'vin' => strtoupper($this->faker->bothify('??###########??')),
            'year_of_manufacture' => $this->faker->numberBetween(1995, now()->year),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
