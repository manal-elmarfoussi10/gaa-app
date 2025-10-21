<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnitPackage;
use App\Models\VirementRequest;

class UnitController extends Controller
{
    /**
     * Show the purchase form for tenant admins.
     * Pulls the current HT price from the active UnitPackage.
     */
    public function showPurchaseForm()
    {
        // Avoid relying on a scope; use a plain where so it works immediately
        $package = UnitPackage::where('is_active', true)->latest('id')->first();

        if (!$package) {
            return back()->with('error', "Aucun pack actif n’est configuré.");
        }

        $vatRate = 20; // % — if you later store VAT on the package, read it from there

        return view('units.purchase', [
            'unitPrice' => (float) $package->price_ht, // HT per unit
            'vatRate'   => $vatRate,                   // percent
        ]);
    }

    /**
     * Handle a new virement (bank transfer) request from a tenant admin.
     * Persists to columns that EXIST in your current virement_requests table.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'quantity'       => 'required|integer|min:1',
            'virement_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:8192',
        ]);

        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return back()->with('error', "Aucune entreprise associée à cet utilisateur.");
        }

        // Read the active package price at the moment of the request
        $package = UnitPackage::where('is_active', true)->latest('id')->first();
        if (!$package) {
            return back()->with('error', "Aucun pack actif n’est configuré.");
        }

        $qty      = (int) $request->integer('quantity');
        $unit     = (float) $package->price_ht; // € HT per unit
        $tvaRate  = 20;                         // %
        $subtotal = $qty * $unit;               // HT total the user is expected to pay

        // Upload proof if provided
        $path = $request->hasFile('virement_proof')
            ? $request->file('virement_proof')->store('virements', 'public')
            : null;

        // 🔐 Persist ONLY the columns that exist in your current schema
        // virement_requests: company_id, user_id, quantity, amount_ht, proof_path, status, timestamps
        VirementRequest::create([
            'company_id' => $company->id,
            'user_id'    => $user->id,
            'quantity'   => $qty,
            'amount_ht'  => $subtotal,  // HT (no VAT here)
            'proof_path' => $path,
            'status'     => 'pending',
        ]);

        return back()->with(
            'success',
            "Votre demande de virement a été enregistrée. Nous vérifierons le reçu et créditerons les unités sous peu."
        );
    }
}