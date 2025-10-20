<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VirementRequest;

class UnitController extends Controller
{
    /**
     * Display the purchase form.
     */
    public function showPurchaseForm()
    {
        return view('units.purchase');
    }

    /**
     * Handle a new virement request (bank transfer).
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'quantity'       => 'required|integer|min:1',
            'virement_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:8192',
        ]);

        $user = $request->user();
        $company = $user->company; // ✅ Each user belongs to one company

        if (!$company) {
            return back()->with('error', "Aucune entreprise associée à cet utilisateur.");
        }

        // 💶 Calculate totals
        $qty      = (int) $request->input('quantity');
        $unit     = 10;      // € HT per unit
        $tvaRate  = 20;      // 20%
        $subtotal = $qty * $unit;
        $tva      = (int) round($subtotal * ($tvaRate / 100));
        $total    = $subtotal + $tva;

        // 🧾 Upload proof if provided
        $path = $request->hasFile('virement_proof')
            ? $request->file('virement_proof')->store('virements', 'public')
            : null;

        // 🧱 Create the virement request (pending)
        VirementRequest::create([
            'company_id'  => $company->id,
            'user_id'     => $user->id,
            'quantity'    => $qty,
            'unit_price'  => $unit,
            'tva_rate'    => $tvaRate,
            'total_cents' => $total * 100, // store in cents to avoid float issues
            'proof_path'  => $path,
            'status'      => 'pending',
        ]);

        return back()->with(
            'success',
            'Votre demande de virement a été enregistrée. 
            Notre équipe vérifiera le reçu et ajoutera les unités à votre entreprise sous peu.'
        );
    }
}