<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    public function show()
    {
        $company = Company::first();
        return view('company.profile', compact('company'));
    }

    public function create()
    {
        return view('company.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'commercial_name'     => 'nullable|string|max:255',
            'email'               => 'required|email|max:255',
            'phone'               => 'nullable|string|max:20',
            'siret'               => 'nullable|string|max:50',
            'tva'                 => 'nullable|string|max:50',
            'iban'                => 'nullable|string|max:50',
            'bic'                 => 'nullable|string|max:50',
            'ape'                 => 'nullable|string|max:50',
            'address'             => 'nullable|string|max:255',
            'postal_code'         => 'nullable|string|max:20',
            'city'                => 'nullable|string|max:100',
            'known_by'            => 'nullable|string|max:255',
            'contact_permission'  => 'nullable|string|max:255',
            'garage_type'         => 'nullable|string|max:100',

            // Legal / extras
            'legal_form'          => 'nullable|string|max:255',
            'capital'             => 'nullable|numeric',
            'head_office_address' => 'nullable|string|max:255',
            'rcs_number'          => 'nullable|string|max:255',
            'rcs_city'            => 'nullable|string|max:255',
            'naf_code'            => 'nullable|string|max:50',
            'professional_insurance' => 'nullable|string|max:255',
            'representative'      => 'nullable|string|max:255',
            'tva_regime'          => 'nullable|string|max:255',
            'eco_contribution'    => 'nullable|string|max:255',
            'penalty_rate'        => 'nullable|string|max:50',
            'methode_paiement'    => 'nullable|string|max:255',

            // Files
            'logo'               => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'rib'                => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'kbis'               => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'id_photo_recto'     => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'id_photo_verso'     => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'tva_exemption_doc'  => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'invoice_terms_doc'  => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            // Signature image (prefer png with transparent bg)
            'signature_path'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Save files to public disk
        foreach ([
            'logo', 'rib', 'kbis', 'id_photo_recto', 'id_photo_verso',
            'tva_exemption_doc', 'invoice_terms_doc', 'signature_path',
        ] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('company_files', 'public');
            }
        }

        $company = Company::create($data);

        return redirect()->route('company.profile')
            ->with('success', 'Informations enregistrées.');
    }

    public function edit()
    {
        $company = Company::first();
        return view('company.edit', compact('company'));
    }

    public function update(Request $request)
    {
        $company = Company::firstOrFail();

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'commercial_name'     => 'nullable|string|max:255',
            'email'               => 'required|email|max:255',
            'phone'               => 'nullable|string|max:20',
            'siret'               => 'nullable|string|max:50',
            'tva'                 => 'nullable|string|max:50',
            'iban'                => 'nullable|string|max:50',
            'bic'                 => 'nullable|string|max:50',
            'ape'                 => 'nullable|string|max:50',
            'address'             => 'nullable|string|max:255',
            'postal_code'         => 'nullable|string|max:20',
            'city'                => 'nullable|string|max:100',
            'known_by'            => 'nullable|string|max:255',
            'contact_permission'  => 'nullable|string|max:255',
            'garage_type'         => 'nullable|string|max:100',

            'legal_form'          => 'nullable|string|max:255',
            'capital'             => 'nullable|numeric',
            'head_office_address' => 'nullable|string|max:255',
            'rcs_number'          => 'nullable|string|max:255',
            'rcs_city'            => 'nullable|string|max:255',
            'naf_code'            => 'nullable|string|max:50',
            'professional_insurance' => 'nullable|string|max:255',
            'representative'       => 'nullable|string|max:255',
            'tva_regime'           => 'nullable|string|max:255',
            'eco_contribution'     => 'nullable|string|max:255',
            'penalty_rate'         => 'nullable|string|max:50',
            'methode_paiement'     => 'nullable|string|max:255',

            'logo'               => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'rib'                => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'kbis'               => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'id_photo_recto'     => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'id_photo_verso'     => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'tva_exemption_doc'  => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'invoice_terms_doc'  => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'signature_path'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        foreach ([
            'logo', 'rib', 'kbis', 'id_photo_recto', 'id_photo_verso',
            'tva_exemption_doc', 'invoice_terms_doc', 'signature_path',
        ] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('company_files', 'public');
            }
        }

        $company->update($data);

        return redirect()->route('company.profile')
            ->with('success', 'Informations mises à jour avec succès.');
    }
}