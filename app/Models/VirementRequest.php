<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirementRequest extends Model
{
    // app/Models/VirementRequest.php
protected $fillable = [
    'company_id','user_id','quantity','proof_path','status',
    // keep your other fields if you added them (unit_price, tva_rate, total_cents, etc.)
];

public function company() { return $this->belongsTo(\App\Models\Company::class); }
public function user()    { return $this->belongsTo(\App\Models\User::class);   }
}
