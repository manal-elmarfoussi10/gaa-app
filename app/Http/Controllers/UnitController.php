<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VirementRequest;
use App\Models\UnitPackage; // ✅ make sure this model exists

class UnitController extends Controller
{
    /**
     * Purchase form (shows live price coming from the active UnitPackage)
     */
    public function showPurchaseForm()
    {
        // Get active pack, or the last configured one if none is active
        $pack = UnitPackage::where('is_active', true)->latest()->first()
            ?? UnitPackage::latest()->first();

        if (!$pack) {
            return back()->with('error', "Aucun pack d’unités n’a été défini par le Super Admin.");
        }

        // You said VAT is 20% globally
        $tvaRate = 20;

        return view('units.purchase', [
            'pack'    => $pack,
            'tvaRate' => $tvaRate,
        ]);
    }

    /**
     * Create the virement request with the current UnitPackage price
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

        // Get pricing from the active pack
        $pack = UnitPackage::where('is_active', true)->latest()->first()
            ?? UnitPackage::latest()->first();

        if (!$pack) {
            return back()->with('error', "Aucun pack d’unités n’a été défini par le Super Admin.");
        }

        $qty      = (int) $request->input('quantity');
        $unitHt   = (float) $pack->price_ht; // € HT / unité
        $tvaRate  = 20; // %
        $subtotal = $qty * $unitHt;
        $tva      = round($subtotal * ($tvaRate / 100), 2);
        $total    = $subtotal + $tva;

        $path = $request->hasFile('virement_proof')
            ? $request->file('virement_proof')->store('virements', 'public')
            : null;

        VirementRequest::create([
            'company_id'  => $company->id,
            'user_id'     => $user->id,
            'quantity'    => $qty,
            'unit_price'  => $unitHt,   // ✅ from Super Admin pack
            'tva_rate'    => $tvaRate,  // 20
            'total_cents' => (int) round($total * 100),
            'proof_path'  => $path,
            'status'      => 'pending',
        ]);

        return back()->with(
            'success',
            "Votre demande de virement a été enregistrée. Notre équipe vérifiera le reçu et ajoutera les unités sous peu."
        );
    }
}