<?php

namespace App\Exports;

use App\Models\Facture;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FacturesExport implements FromCollection, WithHeadings
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    private function isSuperAdmin(): bool
    {
        $user = $this->user;
        if (method_exists($user, 'isSuperAdmin')) {
            return (bool) $user->isSuperAdmin();
        }
        return in_array(($user->role ?? null), ['superadmin', 'SUPERADMIN', 'SuperAdmin'], true);
    }

    public function collection()
    {
        $query = Facture::with('client');

        if (!$this->isSuperAdmin()) {
            $query->where('company_id', $this->user->company_id);
        }

        return $query->get()->map(function ($facture) {
            return [
                'numero' => $facture->numero,
                'date_facture' => $facture->date_facture,
                'total_ht' => $facture->total_ht,
                'total_tva' => $facture->total_tva,
                'total_ttc' => $facture->total_ttc,
                'statut' => $facture->is_paid ? 'Payée' : 'Non payée',
                'client' => $facture->client ? ($facture->client->nom ?? $facture->client->prenom ?? 'N/A') : ($facture->prospect_name ?? 'N/A'),
                'prospect_name' => $facture->prospect_name,
                'prospect_email' => $facture->prospect_email,
                'prospect_phone' => $facture->prospect_phone,
                'prospect_address' => $facture->prospect_address,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Numéro',
            'Date Facture',
            'Total HT',
            'TVA',
            'Total TTC',
            'Statut',
            'Client',
            'Prospect Nom',
            'Prospect Email',
            'Prospect Téléphone',
            'Prospect Adresse'
        ];
    }
}
