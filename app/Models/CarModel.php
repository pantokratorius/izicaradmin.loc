<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $fillable = ['name', 'car_brand_id'];

    public function brand()
    {
        return $this->belongsTo(CarBrand::class);
    }

    public function generations()
    {
        return $this->hasMany(CarGeneration::class);
    }

    public function series()
    {
        return $this->hasMany(CarSerie::class);
    }

    public function modifications()
    {
        return $this->hasMany(CarModification::class);
    }
}
