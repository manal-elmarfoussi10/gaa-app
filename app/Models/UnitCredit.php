<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitCredit extends Model
{
    protected $fillable = [
        'company_id','created_by','units','source','virement_request_id','note',
    ];

    public function company()   { return $this->belongsTo(\App\Models\Company::class); }
    public function author()    { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    public function virement()  { return $this->belongsTo(\App\Models\VirementRequest::class, 'virement_request_id'); }
}