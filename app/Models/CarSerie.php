<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarSerie extends Model
{
    protected $fillable = ['name', 'car_model_id', 'car_generation_id'];

    public function model()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function generation()
    {
        return $this->belongsTo(CarGeneration::class);
    }

    public function modifications()
    {
        return $this->hasMany(CarModification::class);
    }
}
