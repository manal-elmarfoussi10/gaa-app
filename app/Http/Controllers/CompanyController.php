<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Profile page — can be empty if no company yet.
     */
    public function show()
    {
        $company = Company::first();
        return view('company.profile', compact('company'));
    }

    /**
     * Create form (first-time setup).
     */
    public function create()
    {
        // If one already exists, redirect to edit to avoid duplicates.
        if (Company::first()) {
            return redirect()->route('company.edit');
        }
        return view('company.create');
    }

    /**
     * Persist a new company.
     */
    public function store(Request $request)
    {
        // Basic validation
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'commercial_name'    => 'nullable|string|max:255',
            'email'              => 'required|email|max:255',
            'phone'              => 'nullable|string|max:20',
            'siret'              => 'nullable|string|max:50',
            'tva'                => 'nullable|string|max:50',
            'iban'               => 'nullable|string|max:50',
            'bic'                => 'nullable|string|max:50',
            'ape'                => 'nullable|string|max:50',
            'address'            => 'nullable|string|max:255',
            'postal_code'        => 'nullable|string|max:20',
            'city'               => 'nullable|string|max:100',
            'known_by'           => 'nullable|string|max:255',
            'contact_permission' => 'nullable|string|max:255',
            'garage_type'        => 'nullable|string|max:100',

            // New legal/business fields
            'legal_form'             => 'nullable|string|max:255',
            'capital'                => 'nullable|numeric',
            'head_office_address'    => 'nullable|string|max:255',
            'rcs_number'             => 'nullable|string|max:255',
            'rcs_city'               => 'nullable|string|max:255',
            'naf_code'               => 'nullable|string|max:50',
            'professional_insurance' => 'nullable|string|max:255',
            'representative'         => 'nullable|string|max:255',
            'tva_regime'             => 'nullable|string|max:255',
            'eco_contribution'       => 'nullable|string|max:255',
            'penalty_rate'           => 'nullable|string|max:50',
            'methode_paiement'       => 'nullable|string|max:255',

            // Files (keep light validation to match your current setup)
            'logo'               => 'nullable|file',
            'signature_image'    => 'nullable|file', // <-- NEW
            'rib'                => 'nullable|file',
            'kbis'               => 'nullable|file',
            'id_photo_recto'     => 'nullable|file',
            'id_photo_verso'     => 'nullable|file',
            'tva_exemption_doc'  => 'nullable|file',
            'invoice_terms_doc'  => 'nullable|file',
        ]);

        // Handle uploads
        $this->handleUploads($request, $data);

        $company = Company::create($data);

        return redirect()
            ->route('company.profile')
            ->with('success', 'Informations enregistrées.');
    }

    /**
     * Edit form; auto-creates an empty company to avoid nulls.
     */
    public function edit()
    {
        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'name'  => '',
                'email' => '',
            ]);
        }
        return view('company.edit', compact('company'));
    }

    /**
     * Update the company.
     */
    public function update(Request $request)
    {
        $company = Company::first();
        if (!$company) {
            // If somehow none exists, create one and reuse the same validator
            return $this->store($request);
        }

        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'commercial_name'    => 'nullable|string|max:255',
            'email'              => 'required|email|max:255',
            'phone'              => 'nullable|string|max:20',
            'siret'              => 'nullable|string|max:50',
            'tva'                => 'nullable|string|max:50',
            'iban'               => 'nullable|string|max:50',
            'bic'                => 'nullable|string|max:50',
            'ape'                => 'nullable|string|max:50',
            'address'            => 'nullable|string|max:255',
            'postal_code'        => 'nullable|string|max:20',
            'city'               => 'nullable|string|max:100',
            'known_by'           => 'nullable|string|max:255',
            'contact_permission' => 'nullable|string|max:255',
            'garage_type'        => 'nullable|string|max:100',

            // New legal/business fields
            'legal_form'             => 'nullable|string|max:255',
            'capital'                => 'nullable|numeric',
            'head_office_address'    => 'nullable|string|max:255',
            'rcs_number'             => 'nullable|string|max:255',
            'rcs_city'               => 'nullable|string|max:255',
            'naf_code'               => 'nullable|string|max:50',
            'professional_insurance' => 'nullable|string|max:255',
            'representative'         => 'nullable|string|max:255',
            'tva_regime'             => 'nullable|string|max:255',
            'eco_contribution'       => 'nullable|string|max:255',
            'penalty_rate'           => 'nullable|string|max:50',
            'methode_paiement'       => 'nullable|string|max:255',

            // Files
            'logo'               => 'nullable|file',
            'signature_image'    => 'nullable|file', // <-- NEW
            'rib'                => 'nullable|file',
            'kbis'               => 'nullable|file',
            'id_photo_recto'     => 'nullable|file',
            'id_photo_verso'     => 'nullable|file',
            'tva_exemption_doc'  => 'nullable|file',
            'invoice_terms_doc'  => 'nullable|file',
        ]);

        $this->handleUploads($request, $data);

        $company->update($data);

        return redirect()
            ->route('company.profile')
            ->with('success', 'Informations mises à jour avec succès.');
    }

    /**
     * Centralized file upload handler (keeps your path style).
     *
     * NOTE: Your blades use: asset('/storage/app/public/'.$path)
     * So we store to disk 'public' and keep only the relative path (e.g. company_files/xxx.pdf).
     */
    protected function handleUploads(Request $request, array &$data): void
    {
        $fileFields = [
            'logo',
            'signature_image',   // <-- NEW
            'rib',
            'kbis',
            'id_photo_recto',
            'id_photo_verso',
            'tva_exemption_doc',
            'invoice_terms_doc',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('company_files', 'public');
            }
        }
    }
}