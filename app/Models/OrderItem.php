<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{

    use HasFactory;
    protected $fillable = [
        'order_id',
        'part_number',
        'part_make',
        'part_name',
        'sale_price',
        'purchase_price',
        'supplier',
        'prepayment',
        'quantity',
        'status',
    ];


    protected $attributes = [
        'prepayment' => 0,
    ];

    // Связь: одна запчасть принадлежит одному заказу
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
