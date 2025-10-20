<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitPackage extends Model
{
    protected $fillable = [
        'name',
        'units',       // keep = 1 (price is for 1 unit)
        'price_ht',    // HT price per unit
        'is_active',
    ];

    protected $casts = [
        'units'     => 'int',
        'price_ht'  => 'float',
        'is_active' => 'bool',
    ];
    public function getPriceTtcAttribute(): float {
        return round($this->price_ht * 1.2, 2);
    }
}