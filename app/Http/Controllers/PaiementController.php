<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Paiement;
use App\Models\ClientHistory;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function create(Request $request)
    {
        $facture_id = $request->facture_id; // passed in URL
        $facture = Facture::findOrFail($facture_id);

        return view('paiements.create', compact('facture'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facture_id' => 'required|exists:factures,id',
            'montant' => 'required|numeric|min:0',
            'mode' => 'nullable|string',
            'date' => 'required|date',
            'commentaire' => 'nullable|string'
        ]);

        Paiement::create($validated);

        // Check if fully paid
        $facture = Facture::with(['paiements', 'avoirs', 'client'])->find($validated['facture_id']);
        if ($facture) {
            if ($facture->client) {
                $facture->client->histories()->create([
                    'status_type'  => 'paiement',
                    'status_value' => 'Paiement reçu',
                    'description'  => "Enregistrement d'un paiement de {$validated['montant']}€ (Mode: {$validated['mode']}) pour la facture n°{$facture->numero}.",
                ]);
            }

            $totalPaye  = $facture->paiements->sum('montant');
            $totalAvoir = $facture->avoirs->sum('montant');
            $totalDu    = $facture->total_ttc;

            if (round($totalPaye + $totalAvoir, 2) >= round($totalDu, 2)) {
                if ($facture->client) {
                    $facture->client->update(['statut' => 'Payé / Acquitté']);
                    
                    $facture->client->histories()->create([
                        'status_type'  => 'statut',
                        'status_value' => 'Dossier clôturé automatiquement',
                        'description'  => "Le dossier a été clôturé automatiquement suite au paiement intégral de la facture n°{$facture->numero}.",
                    ]);
                }
            }
        }

        return redirect()->route('factures.index')->with('success', 'Paiement enregistré');

    }
}
