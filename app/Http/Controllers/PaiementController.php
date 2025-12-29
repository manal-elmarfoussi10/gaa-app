<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Paiement;
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
            $totalPaye  = $facture->paiements->sum('montant');
            $totalAvoir = $facture->avoirs->sum('montant');
            $totalDu    = $facture->total_ttc;

            if (round($totalPaye + $totalAvoir, 2) >= round($totalDu, 2)) {
                if ($facture->client) {
                    $facture->client->update(['statut' => 'Payé / Acquitté']);
                }
            }
        }

        return redirect()->route('factures.index')->with('success', 'Paiement enregistré');

    }
}
