<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'amount',
        'created_at_order',
        'vehicle_id',
        'client_id',
        'manager_id',
        'mileage',
    ];

    // An order may belong to a vehicle (optional)
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // An order may belong to a client directly
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // The manager responsible for the order
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
