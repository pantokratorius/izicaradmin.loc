<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DraftOrder extends Model
{
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
