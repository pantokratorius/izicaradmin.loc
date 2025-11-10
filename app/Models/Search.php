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


     // In Search model
    public function getAmountAttribute()
    {
        $globalMargin = Setting::first()->margin ?? 0;

        // Use sell_price if available, otherwise calculate with margin
        $base = $this->sell_price > 0
            ? $this->sell_price
            : $this->purchase_price * (1 + $globalMargin / 100);

        // Multiply by quantity
        $total = $base * ($this->quantity ?? 1);

        // Round up to nearest 0.5
        return ceil($total / 0.5) * 0.5;
    }

     public static function getSummAttribute()
    {
        $globalMargin = Setting::first()->margin ?? 0;

        $total = self::all()->sum(function ($item) use ($globalMargin) {
            $base = $item->sell_price > 0
                ? $item->sell_price
                : $item->purchase_price * (1 + $globalMargin / 100);

            $sum = $base * ($item->quantity ?? 1);

            return ceil($sum / 0.5) * 0.5;
        });

        return $total;
    }




}
