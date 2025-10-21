<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\UnitPackage;
use App\Models\VirementRequest;

class UnitController extends Controller
{
    /**
     * Display the purchase form (uses price from the active UnitPackage).
     */
    public function showPurchaseForm()
    {
        // Use the scope we added (or swap to where('is_active', true))
        $package = UnitPackage::active()->latest('id')->first();

        if (!$package) {
            return back()->with('error', "Aucun pack actif n’est configuré.");
        }

        $vatRate = 20; // % — if you later store VAT in the package, read it there

        return view('units.purchase', [
            'unitPrice' => $package->price_ht, // HT per unit
            'vatRate'   => $vatRate,           // percent
        ]);
    }

    /**
     * Handle a new virement (bank transfer) request from a tenant admin.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'quantity'       => 'required|integer|min:1',
            'virement_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:8192',
        ]);

        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return back()->with('error', "Aucune entreprise associée à cet utilisateur.");
        }

        // Read the active package price at the moment of the request
        $package = UnitPackage::active()->latest('id')->first();
        if (!$package) {
            return back()->with('error', "Aucun pack actif n’est configuré.");
        }

        $qty      = (int) $request->integer('quantity');
        $unit     = (float) $package->price_ht; // € HT per unit
        $tvaRate  = 20;                         // %
        $subtotal = $qty * $unit;               // HT
        $tva      = round($subtotal * ($tvaRate / 100), 2);
        $total    = $subtotal + $tva;

        // Upload proof if provided
        $path = $request->hasFile('virement_proof')
            ? $request->file('virement_proof')->store('virements', 'public')
            : null;

        // Persist the request (keep the price used at this time!)
        VirementRequest::create([
            'company_id'  => $company->id,
            'user_id'     => $user->id,
            'quantity'    => $qty,
            'unit_price'  => $unit,          // <— store unit price snapshot
            'tva_rate'    => $tvaRate,       // %
            'total_cents' => (int) round($total * 100),
            'proof_path'  => $path,
            'status'      => 'pending',
        ]);

        return back()->with(
            'success',
            "Votre demande de virement a été enregistrée. Nous vérifierons le reçu et créditerons les unités sous peu."
        );
    }
}