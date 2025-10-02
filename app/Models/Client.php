<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\CompanyScoped;

class Client extends Model
{
    use CompanyScoped;

    protected $fillable = [
        // Identity & contact
        'nom_assure', 'prenom', 'email', 'telephone', 'adresse',
        'code_postal', 'ville',

        // Vehicle / insurance
        'plaque', 'nom_assurance', 'autre_assurance',
        'ancien_modele_plaque', 'numero_police',
        'date_sinistre', 'date_declaration', 'raison',
        'type_vitrage', 'professionnel', 'reparation',
        'kilometrage',

        // Files (photos)
        'photo_vitrage', 'photo_carte_verte', 'photo_carte_grise',

        // Misc
        'type_cadeau', 'numero_sinistre', 'connu_par', 'adresse_pose',
        'reference_interne', 'reference_client', 'precision',
        'statut', 'company_id',

        // Contract files
        'contract_pdf_path',           // unsigned contract
        'signed_pdf_path',             // legacy column for signed pdf
        'contract_signed_pdf_path',    // NEW preferred column for signed pdf

        // Yousign tracking
        'yousign_signature_request_id',
        'yousign_document_id',
        'yousign_procedure_id',
        'yousign_file_id',

        // Signature status
        'statut_gsauto',
        'statut_signature',    // tinyint(1) in DB -> cast to bool below
        'statut_termine',      // tinyint(1) in DB -> cast to bool below

        // Timestamps
        'signed_at',
    ];

    protected $casts = [
        'signed_at'        => 'datetime',
        'statut_signature' => 'boolean',
        'statut_termine'   => 'boolean',
    ];

    // ---------- Relationships ----------
    public function rdvs()               { return $this->hasMany(Rdv::class); }
    public function factures()           { return $this->hasMany(Facture::class); }
    public function avoirs()             { return $this->hasManyThrough(\App\Models\Avoir::class, \App\Models\Facture::class, 'client_id', 'facture_id', 'id', 'id'); }
    public function devis()              { return $this->hasMany(Devis::class); }
    public function photos()             { return $this->hasMany(Photo::class); }
    public function expenses()           { return $this->hasMany(Expense::class); }
    public function bondecommandes()     { return $this->hasMany(BonDeCommande::class); }
    public function conversations()      { return $this->hasMany(ConversationThread::class); }
    public function interventions()      { return $this->hasMany(Intervention::class); }
    public function company()            { return $this->belongsTo(Company::class); }

    // ---------- Accessors ----------
    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom_assure ?? ''));
    }

    public function getContractSignedPdfPathAttribute($value)
    {
        // Prefer the new column; fall back to legacy if present
        return $value ?? $this->attributes['signed_pdf_path'] ?? null;
    }

    public function getYousignRequestIdAttribute($value)
    {
        return $value ?? $this->attributes['yousign_signature_request_id'] ?? null;
    }

    public function emails()
{
    // Use FQCN to avoid missing import issues
    return $this->hasMany(\App\Models\Email::class, 'client_id');
}
}