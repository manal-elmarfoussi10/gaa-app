<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Base
            'name'            => ['required','string','max:255'],
            'commercial_name' => ['nullable','string','max:255'],
            'email'           => ['nullable','email','max:255'],
            'phone'           => ['nullable','string','max:50'],

            // Adresse
            'address'     => ['nullable','string','max:255'],
            'postal_code' => ['nullable','string','max:20'],
            'city'        => ['nullable','string','max:100'],

            // Légal
            'legal_form'   => ['nullable','string','max:255'],
            'capital'      => ['nullable','numeric'],
            'siret'        => ['nullable','string','max:50'],
            'tva'          => ['nullable','string','max:50'],
            'rcs_number'   => ['nullable','string','max:255'],
            'rcs_city'     => ['nullable','string','max:255'],
            'ape'          => ['nullable','string','max:50'],
            'naf_code'     => ['nullable','string','max:50'],

            // Paiement
            'payment_method' => ['nullable','string','max:255'],
            'iban'           => ['nullable','string','max:50'],
            'bic'            => ['nullable','string','max:50'],
            'penalty_rate'   => ['nullable','string','max:50'],

            // Divers (⚠️ dans ta base ces colonnes peuvent ne pas être NULL; on les passe au moins en string vide)
            'known_by'            => ['nullable','string','max:255'],
            'contact_permission'  => ['nullable','string','max:255'],
            'garage_type'         => ['nullable','string','max:100'],
            'representative'      => ['nullable','string','max:255'],
            'professional_insurance' => ['nullable','string','max:255'],
            'tva_regime'          => ['nullable','string','max:255'],
            'eco_contribution'    => ['nullable','string','max:255'],

            // Fichiers
            'logo'               => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'rib'                => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'kbis'               => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'id_photo_recto'     => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'id_photo_verso'     => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'tva_exemption_doc'  => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'invoice_terms_doc'  => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'signature_path'     => ['nullable','image','mimes:png,jpg,jpeg','max:2048'],

            // Toggle création admin
            'create_admin' => ['nullable'],
        ];

        // Si on crée aussi un utilisateur, exiger ses champs
        if ($this->boolean('create_admin')) {
            $rules += [
                'admin.first_name'            => ['required','string','max:100'],
                'admin.last_name'             => ['required','string','max:100'],
                'admin.email'                 => ['required','email','max:255'],
                'admin.role'                  => ['required','string'],
                'admin.password'              => ['required','string','min:8','confirmed'],
                'admin.password_confirmation' => ['required','string','min:8'],
                'admin.is_active'             => ['nullable'],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'admin.first_name.required' => 'Vous devez ajouter un utilisateur administrateur pour la société (prénom).',
            'admin.last_name.required'  => 'Vous devez ajouter un utilisateur administrateur pour la société (nom).',
            'admin.email.required'      => 'Vous devez ajouter un utilisateur administrateur pour la société (email).',
            'admin.role.required'       => 'Vous devez sélectionner le rôle du premier utilisateur.',
            'admin.password.required'   => 'Vous devez définir un mot de passe pour le premier utilisateur.',
            'admin.password.confirmed'  => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}