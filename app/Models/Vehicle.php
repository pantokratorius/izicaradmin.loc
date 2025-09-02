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
        'brand',
        'model',
        'generation',
        'body',
        'modification',
        'registration_number',
        'sts',
        'pts',
        'year_of_manufacture',
        'engine_type',
        'client_id',
    ];


  public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
