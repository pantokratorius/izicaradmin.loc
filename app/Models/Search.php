<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    protected $fillable = [
        'name',
        'part_make',
        'part_number',
        'quantity',
        'reserved',
        'sell_price',
        'warehouse',
        'purchase_price',
        'supplier',
    ];
}
