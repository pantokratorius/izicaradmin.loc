<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

      protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'phone', 'email', 'segment', 'discount', 'comment' 
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

      public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
