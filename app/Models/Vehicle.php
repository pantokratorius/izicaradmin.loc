<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vin',
        'vehicle_type',
        // 'brand',
        // 'model',
        // 'generation',
        // 'body',
        // 'modification',
        'registration_number',
        'sts',
        'pts',
        'year_of_manufacture',
        'engine_type',
        'client_id',

        'car_brand_id',
        'car_model_id',
        'car_generation_id',
        'car_serie_id',
        'car_modification_id',
    ];


  public function client()
    {
        return $this->belongsTo(Client::class);
    }

     public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }   

    public function generation()
    {
        return $this->belongsTo(CarGeneration::class, 'car_generation_id');
    }

    public function serie()
    {
        return $this->belongsTo(CarSerie::class, 'car_serie_id');
    }

    public function modification()
    {
        return $this->belongsTo(CarModification::class, 'car_modification_id');
    }


}
