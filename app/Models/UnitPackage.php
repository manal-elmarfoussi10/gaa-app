<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitPackage extends Model
{
    protected $table = 'unit_packages';

    protected $fillable = [
        'name',
        'units',
        'price_ht',
        'is_active',
    ];

    protected $casts = [
        'price_ht' => 'float',
        'is_active' => 'bool',
    ];

    // <— Add this so you can call UnitPackage::active()
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}