<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientHistory extends Model
{
    protected $table = 'client_histories';

    protected $fillable = [
        'client_id',
        'status_type',
        'status_value',
        'description',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
