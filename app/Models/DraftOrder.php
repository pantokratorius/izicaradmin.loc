<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DraftOrder extends Model
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

    protected $table = 'orders';

    protected static function booted()
    {
        static::addGlobalScope('onlyDrafts', function (Builder $builder) {
            $builder->where('status', 0);
        });
    }

    // relationships duplicated from Order
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // your getters (copy them exactly)
}
