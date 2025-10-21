<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::latest()->paginate(12);
        return view('superadmin.companies.index', compact('companies'));
    }

    public function create()
    {
        // Rôles affichés (sans superadmin)
        $roles = collect(User::roles())->except(User::ROLE_SUPERADMIN)->toArray();
        return view('superadmin.companies.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // ----- 1) Validate base company fields
        $rules = [
            'name'               => ['required','string','max:255'],
            'commercial_name'    => ['nullable','string','max:255'],
            'email'              => ['nullable','email','max:255'],
            'phone'              => ['nullable','string','max:50'],

            // Adresse
            'address'            => ['nullable','string','max:255'],
            'postal_code'        => ['nullable','string','max:20'],
            'city'               => ['nullable','string','max:100'],

            // Légal
            'legal_form'         => ['nullable','string','max:255'],
            'capital'            => ['nullable','numeric'],
            'siret'              => ['nullable','string','max:50'],
            'tva'                => ['nullable','string','max:50'],
            'rcs_number'         => ['nullable','string','max:255'],
            'rcs_city'           => ['nullable','string','max:255'],
            'ape'                => ['nullable','string','max:50'],
            'naf_code'           => ['nullable','string','max:50'],

            // Paiement
            'payment_method'     => ['nullable','string','max:255'],
            'iban'               => ['nullable','string','max:50'],
            'bic'                => ['nullable','string','max:50'],
            'penalty_rate'       => ['nullable','string','max:50'],

            // Divers
            'known_by'           => ['nullable','string','max:255'],
            'contact_permission' => ['nullable','string','max:255'],
            'garage_type'        => ['nullable','string','max:100'],
            'representative'     => ['nullable','string','max:255'],
            'professional_insurance' => ['nullable','string','max:255'],
            'tva_regime'         => ['nullable','string','max:255'],
            'eco_contribution'   => ['nullable','string','max:255'],

            // Fichiers
            'logo'               => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'rib'                => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'kbis'               => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'id_photo_recto'     => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'id_photo_verso'     => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'tva_exemption_doc'  => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'invoice_terms_doc'  => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'signature_path'     => ['nullable','image','mimes:png,jpg,jpeg','max:2048'],

            // Checkbox utilisateur
            'create_admin'       => ['sometimes','boolean'],
        ];

        // ----- 2) Admin required if create_admin checked
        if ($request->boolean('create_admin')) {
            $rules = array_merge($rules, [
                'admin.first_name'            => ['required','string','max:255'],
                'admin.last_name'             => ['required','string','max:255'],
                'admin.email'                 => ['required','email','max:255', Rule::unique('users','email')],
                'admin.role'                  => ['required', Rule::in(array_keys(User::roles()))],
                'admin.password'              => ['required','string','min:8','confirmed'],
                'admin.password_confirmation' => ['required','string','min:8'],
                'admin.is_active'             => ['sometimes','boolean'],
            ]);
        }

        $messages = [
            'admin.first_name.required' => "Veuillez renseigner le prénom de l’utilisateur.",
            'admin.last_name.required'  => "Veuillez renseigner le nom de l’utilisateur.",
            'admin.email.required'      => "Veuillez renseigner l’email de l’utilisateur.",
            'admin.email.unique'        => "Cet email est déjà utilisé.",
            'admin.role.required'       => "Veuillez choisir un rôle pour l’utilisateur.",
            'admin.password.required'   => "Veuillez définir un mot de passe pour l’utilisateur.",
            'admin.password.confirmed'  => "La confirmation du mot de passe ne correspond pas.",
        ];

        $data = $request->validate($rules, $messages);

        // ----- 3) Normalize string fields that must not be NULL in DB
        $stringFields = [
            'commercial_name','email','phone','address','postal_code','city',
            'legal_form','siret','tva','rcs_number','rcs_city','ape','naf_code',
            'payment_method','iban','bic','penalty_rate',
            'known_by','contact_permission','garage_type','representative',
            'professional_insurance','tva_regime','eco_contribution',
        ];
        foreach ($stringFields as $key) {
            if (!array_key_exists($key, $data) || $data[$key] === null) {
                $data[$key] = ''; // ← évite l’erreur "cannot be null"
            }
        }

        // ----- 4) Handle files
        foreach ([
            'logo','signature_path','rib','kbis','id_photo_recto','id_photo_verso',
            'tva_exemption_doc','invoice_terms_doc',
        ] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('company_files', 'public');
            } else {
                unset($data[$field]); // ne pas écraser avec null
            }
        }

        // ----- 5) Create (company + optional user) atomically
        $company = null;

        DB::transaction(function () use (&$company, $data, $request) {
            // champs num / spéciaux
            $payload = [
                'name'                 => $data['name'],
                'commercial_name'      => $data['commercial_name'] ?? '',
                'email'                => $data['email'] ?? '',
                'phone'                => $data['phone'] ?? '',
                'address'              => $data['address'] ?? '',
                'postal_code'          => $data['postal_code'] ?? '',
                'city'                 => $data['city'] ?? '',
                'legal_form'           => $data['legal_form'] ?? '',
                'capital'              => $data['capital'] ?? null,
                'siret'                => $data['siret'] ?? '',
                'tva'                  => $data['tva'] ?? '',
                'rcs_number'           => $data['rcs_number'] ?? '',
                'rcs_city'             => $data['rcs_city'] ?? '',
                'ape'                  => $data['ape'] ?? '',
                'naf_code'             => $data['naf_code'] ?? '',
                'payment_method'       => $data['payment_method'] ?? '',
                'iban'                 => $data['iban'] ?? '',
                'bic'                  => $data['bic'] ?? '',
                'penalty_rate'         => $data['penalty_rate'] ?? '',
                'known_by'             => $data['known_by'] ?? '',
                'contact_permission'   => $data['contact_permission'] ?? '',
                'garage_type'          => $data['garage_type'] ?? '',
                'representative'       => $data['representative'] ?? '',
                'professional_insurance'=> $data['professional_insurance'] ?? '',
                'tva_regime'           => $data['tva_regime'] ?? '',
                'eco_contribution'     => $data['eco_contribution'] ?? '',
            ];

            // Fichiers
            foreach ([
                'logo','signature_path','rib','kbis','id_photo_recto','id_photo_verso',
                'tva_exemption_doc','invoice_terms_doc',
            ] as $f) {
                if (isset($data[$f])) {
                    $payload[$f] = $data[$f];
                }
            }

            $company = Company::create($payload);

            // Utilisateur optionnel
            if ($request->boolean('create_admin')) {
                $a = $data['admin'];

                $user = new User();
                $user->first_name = $a['first_name'];
                $user->last_name  = $a['last_name'];
                $user->name       = trim($a['first_name'].' '.$a['last_name']);
                $user->email      = $a['email'];
                $user->role       = $a['role'];
                $user->company_id = $company->id;
                $user->is_active  = isset($a['is_active']) ? (bool)$a['is_active'] : true;
                $user->password   = Hash::make($a['password']);
                $user->save();
            }
        });

        return redirect()
            ->route('superadmin.companies.show', $company)
            ->with('success', 'Société créée avec succès.');
    }

    public function show(Company $company)
    {
        $users = $company->users()->orderBy('role')->get();
        return view('superadmin.companies.show', compact('company','users'));
    }

    public function edit(Company $company)
    {
        return view('superadmin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $rules = [
            'name'               => ['required','string','max:255'],
            'commercial_name'    => ['nullable','string','max:255'],
            'email'              => ['nullable','email','max:255'],
            'phone'              => ['nullable','string','max:50'],
            'address'            => ['nullable','string','max:255'],
            'postal_code'        => ['nullable','string','max:20'],
            'city'               => ['nullable','string','max:100'],
            'legal_form'         => ['nullable','string','max:255'],
            'capital'            => ['nullable','numeric'],
            'siret'              => ['nullable','string','max:50'],
            'tva'                => ['nullable','string','max:50'],
            'rcs_number'         => ['nullable','string','max:255'],
            'rcs_city'           => ['nullable','string','max:255'],
            'ape'                => ['nullable','string','max:50'],
            'naf_code'           => ['nullable','string','max:50'],
            'payment_method'     => ['nullable','string','max:255'],
            'iban'               => ['nullable','string','max:50'],
            'bic'                => ['nullable','string','max:50'],
            'penalty_rate'       => ['nullable','string','max:50'],
            'known_by'           => ['nullable','string','max:255'],
            'contact_permission' => ['nullable','string','max:255'],
            'garage_type'        => ['nullable','string','max:100'],
            'representative'     => ['nullable','string','max:255'],
            'professional_insurance' => ['nullable','string','max:255'],
            'tva_regime'         => ['nullable','string','max:255'],
            'eco_contribution'   => ['nullable','string','max:255'],
            'logo'               => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'rib'                => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'kbis'               => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'id_photo_recto'     => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'id_photo_verso'     => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'tva_exemption_doc'  => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'invoice_terms_doc'  => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:4096'],
            'signature_path'     => ['nullable','image','mimes:png,jpg,jpeg','max:2048'],
        ];

        $data = $request->validate($rules);

        // no NULL into NOT NULL string columns
        foreach ([
            'commercial_name','email','phone','address','postal_code','city',
            'legal_form','siret','tva','rcs_number','rcs_city','ape','naf_code',
            'payment_method','iban','bic','penalty_rate','known_by','contact_permission',
            'garage_type','representative','professional_insurance','tva_regime','eco_contribution',
        ] as $key) {
            if (!array_key_exists($key, $data) || $data[$key] === null) {
                $data[$key] = '';
            }
        }

        foreach ([
            'logo','signature_path','rib','kbis','id_photo_recto','id_photo_verso',
            'tva_exemption_doc','invoice_terms_doc',
        ] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('company_files', 'public');
            } else {
                unset($data[$field]);
            }
        }

        $company->update($data);

        return redirect()
            ->route('superadmin.companies.show', $company)
            ->with('success', 'Société mise à jour.');
    }

    public function destroy(Company $company)
    {
        $company->users()->delete();
        $company->delete();

        return redirect()
            ->route('superadmin.companies.index')
            ->with('success', 'Société supprimée avec succès.');
    }
}