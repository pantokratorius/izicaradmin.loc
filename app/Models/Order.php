<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{



    use HasFactory;

    protected $fillable = [
        'order_number',
        'amount',
        'created_at',
        'vehicle_id',
        'client_id',
        'manager_id',
        'mileage',
        'prepayment',
        'status',
        'margin',
        'comment',
    ];

    
    protected $attributes = [
        'prepayment' => 0,
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

     public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

       public function getAmountAttribute()
    {
        $globalMargin = Setting::first()->margin ?? 0;
        $discount = $this->client->discount ?? 0;

        $res =  $this->items->sum(function ($item) use ($globalMargin, $discount) {
            // приоритет маржи: item → order → settings
            $margin = $item->margin ?? $this->margin ?? $globalMargin;

            $base = $item->sell_price > 0 ? $item->sell_price * $item->quantity : $item->purchase_price * (1 + $margin / 100) * $item->quantity;

            // применяем скидку клиента
            return $base * (1 - $discount / 100);
        }) ;

        return ceil($res / .5)  * .5;
    }

    public function getPurchaseSumAttribute()
    {
        $res = $this->items->sum(function ($item) {
            return $item->purchase_price * $item->quantity;
        });

        return ceil($res / .5)  * .5;
    }
}
