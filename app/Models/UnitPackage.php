<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitPackage extends Model
{
    protected $fillable = ['name','units','price_ht','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function getPriceTtcAttribute(): float {
        return round($this->price_ht * 1.2, 2);
    }
}