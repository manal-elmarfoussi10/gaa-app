<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitPackage extends Model
{
    protected $fillable = [
        'name',
        'unit_price',
        'vat_rate',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'vat_rate'   => 'float',
        'is_active'  => 'boolean',
    ];
    public function getPriceTtcAttribute(): float {
        return round($this->price_ht * 1.2, 2);
    }
}