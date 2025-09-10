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
        'margin',
    ];


    protected $attributes = [
        'prepayment' => 0,
    ];

    // Связь: одна запчасть принадлежит одному заказу
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

       public function getAmountAttribute()
    {
        $globalMargin = Setting::first()->margin ?? 0;

        // приоритет маржи
        $margin = $this->margin ?? $this->order->margin ?? $globalMargin;

        // цена с маржой
        $base = $this->purchase_price * (1 + $margin / 100) * $this->quantity;

        // скидка клиента
        $discount = $this->order->client->discount ?? 0;

        return $base * (1 - $discount / 100);
    }
}
