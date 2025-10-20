<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirementRequest extends Model
{
    protected $fillable = [
        'user_id','company_id','quantity','amount_ht','proof_path','status','notes'
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(Company::class); }

    public function scopePending($q)  { $q->where('status','pending'); }
    public function scopeApproved($q) { $q->where('status','approved'); }
    public function scopeRejected($q) { $q->where('status','rejected'); }
}