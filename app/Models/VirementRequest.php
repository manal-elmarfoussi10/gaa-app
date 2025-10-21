<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirementRequest extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'quantity',
        'unit_price', 'amount_ht', 'tva_rate', 'total_cents',
        'proof_path', 'status', 'notes',
    ];

    // Relations
    public function company() { return $this->belongsTo(\App\Models\Company::class); }
    public function user()    { return $this->belongsTo(\App\Models\User::class); }

    // ✅ Fallback if some older rows don’t have amount_ht filled
    public function getAmountHtAttribute($value)
    {
        if (!is_null($value)) return $value;
        $qty  = (int) ($this->quantity ?? 0);
        $unit = (float) ($this->unit_price ?? 0);
        return round($qty * $unit, 2);
    }

    // Convenience
    public function getTotalTtcAttribute()
    {
        if (!is_null($this->total_cents)) return $this->total_cents / 100;
        $rate = (float) ($this->tva_rate ?? 0);
        return round($this->amount_ht * (1 + $rate/100), 2);
    }
}