<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModification extends Model
{
    protected $fillable = ['name', 'start_production_year', 'end_production_year', 'car_model_id', 'car_serie_id'];

    public function model()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function serie()
    {
        return $this->belongsTo(CarSerie::class);
    }

    public function characteristics()
    {
        return $this->hasMany(CarCharacteristicValue::class);
    }
}
