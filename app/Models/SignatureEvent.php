<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureEvent extends Model
{
    protected $fillable = [
        'client_id',
        'event_name',
        'yousign_request_id',
        'yousign_document_id',
        'payload',
    ];
}