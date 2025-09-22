<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'name',
        'part_make',
        'part_number',
        'quantity',
        'volume_step',
        'reserved',
        'sell_price',
        'min_stock',
        'warehouse',
        'warehouse_address',
        'purchase_price',
        'tags',
        'marking',
        'categories',
        'address_code',
        'address_name',
    ];
}
