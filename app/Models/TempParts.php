<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempParts extends Model
{
    protected $fillable = [
        'brand', 'article', 'name',  'price', 'quantity',  'user_id'
    ];
}
