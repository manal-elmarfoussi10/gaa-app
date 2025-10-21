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
        $package = UnitPackage::active()->first();
        if (!$package) {
            return back()->with('error', "Aucun pack actif n’est configuré par l’administrateur.");
        }

        $vat = 20; // if you later store VAT in the package, swap here

        return view('units.purchase', [
            'unitPrice' => (float) $package->price_ht,
            'vatRate'   => $vat,
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

        $package = UnitPackage::active()->first();
        if (!$package) {
            return back()->with('error', "Aucun pack actif n’est configuré.");
        }

        $qty      = (int) $request->integer('quantity');
        $unitHt   = (float) $package->price_ht; // ✅ dynamic
        $tvaRate  = 20;                          // or $package->tax_rate if you add one
        $amountHt = round($qty * $unitHt, 2);
        $tva      = round($amountHt * ($tvaRate / 100), 2);
        $total    = $amountHt + $tva;

        $path = $request->hasFile('virement_proof')
            ? $request->file('virement_proof')->store('virements', 'public')
            : null;

        VirementRequest::create([
            'company_id'  => $company->id,
            'user_id'     => $user->id,
            'quantity'    => $qty,
            'unit_price'  => $unitHt,
            'amount_ht'   => $amountHt,          // ✅ store HT
            'tva_rate'    => $tvaRate,
            'total_cents' => (int) round($total * 100),
            'proof_path'  => $path,
            'status'      => 'pending',
        ]);

        return back()->with(
            'success',
            "Votre demande de virement a été enregistrée. Nous la traiterons sous peu."
        );
    }
}