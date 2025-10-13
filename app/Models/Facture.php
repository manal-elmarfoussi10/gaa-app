<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = [
        'client_id',
        'devis_id',
        'titre',
        'date_facture',
        'total_ht',
        'tva',
        'total_tva',
        'total_ttc',
        'is_paid',
        'date_paiement',
        'methode_paiement',
        'payment_method',
        'payment_iban',
        'payment_bic',
        'penalty_rate',
        'payment_terms_text',
        'due_date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function items()
    {
        return $this->hasMany(FactureItem::class);
    }
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
    public function avoirs()
    {
        return $this->hasMany(Avoir::class);
    }
}


