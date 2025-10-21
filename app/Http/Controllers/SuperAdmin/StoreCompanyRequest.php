<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin(); // or return true;
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'commercial_name'     => 'nullable|string|max:255',
            'email'               => 'nullable|email|max:255',
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
            'payment_method'      => 'nullable|string|max:255', // ✅ correct key

            // Files
            'logo'               => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'rib'                => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'kbis'               => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'id_photo_recto'     => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'id_photo_verso'     => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'tva_exemption_doc'  => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'invoice_terms_doc'  => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
            'signature_path'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',

            // Optional “create first user” block
            'create_admin'           => 'nullable|boolean',
            'admin.first_name'       => 'required_with:create_admin|string|max:255',
            'admin.last_name'        => 'required_with:create_admin|string|max:255',
            'admin.email'            => 'required_with:create_admin|email|max:255|unique:users,email',
            'admin.role'             => 'required_with:create_admin|string',
            'admin.password'         => 'required_with:create_admin|confirmed|min:8',
            'admin.is_active'        => 'nullable|boolean',
        ];
    }
}