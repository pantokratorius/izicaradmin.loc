<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarGeneration extends Model
{
    protected $fillable = ['name', 'year_begin', 'year_end', 'car_model_id'];

    public function model()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function series()
    {
        return $this->hasMany(CarSerie::class);
    }
}
