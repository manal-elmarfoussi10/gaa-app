<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['name', 'email', 'message', 'company_id', 'type', 'read'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

