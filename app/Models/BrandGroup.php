<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandGroup extends Model
{
     protected $fillable = ['display_name', 'aliases'];
    protected $casts = [
        'aliases' => 'array',
    ];
}