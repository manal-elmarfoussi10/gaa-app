<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreCompanyRequest;
use App\Http\Requests\SuperAdmin\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::latest()->paginate(12);
        return view('superadmin.companies.index', compact('companies'));
    }

    public function create()
    {
        $roles = collect(User::roles())->except(User::ROLE_SUPERADMIN)->toArray();
        return view('superadmin.companies.create', compact('roles'));
    }

    public function store(\App\Http\Requests\SuperAdmin\StoreCompanyRequest $request)
    {
        // Build from fillable (not only validated keys) to keep optional fields
        $fillable = (new \App\Models\Company())->getFillable();
    
        // Take all request keys that are in fillable
        $data = collect($request->all())->only($fillable)->toArray();
    
        // Normalize empty text fields to "" to avoid NOT NULL violations (e.g. contact_permission)
        foreach ($data as $k => $v) {
            if (is_null($v)) {
                $data[$k] = ''; // safe for VARCHAR/TEXT columns that are NOT NULL
            }
        }
    
        // Files
        foreach ([
            'logo','rib','kbis','id_photo_recto','id_photo_verso',
            'tva_exemption_doc','invoice_terms_doc','signature_path',
        ] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('company_files', 'public');
            }
        }
    
        try {
            $company = null;
    
            \DB::transaction(function () use (&$company, $data, $request) {
                // 1) create company
                $company = \App\Models\Company::create($data);
    
                // 2) optionally create first user
                if ($request->boolean('create_admin')) {
                    $a = $request->input('admin', []);
    
                    $user = new \App\Models\User();
                    $user->first_name = $a['first_name'] ?? '';
                    $user->last_name  = $a['last_name']  ?? '';
                    $user->name       = trim(($a['first_name'] ?? '').' '.($a['last_name'] ?? ''));
                    $user->email      = $a['email'] ?? null;
                    $user->role       = $a['role'] ?? \App\Models\User::ROLE_ADMIN;
                    $user->company_id = $company->id;
                    $user->is_active  = isset($a['is_active']) ? (bool) $a['is_active'] : true;
    
                    // The FormRequest already enforced required admin fields when checkbox is checked
                    $user->password   = \Illuminate\Support\Facades\Hash::make($a['password']);
                    $user->save();
                }
            });
    
            return redirect()
                ->route('superadmin.companies.show', $company)
                ->with('success', 'Société créée avec succès.');
    
        } catch (\Illuminate\Database\QueryException $e) {
            // Friendly message in FR; also log exact DB error for us
            \Log::error('Company STORE failed', ['error' => $e->getMessage(), 'payload' => $data]);
    
            return back()
                ->withInput()
                ->withErrors(['global' => "Impossible de créer la société. Vérifiez les champs obligatoires. ".
                    "Si vous avez coché « Créer aussi un utilisateur », vous devez renseigner l’ensemble de ses informations."]);
        }
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

    public function update(\App\Http\Requests\SuperAdmin\UpdateCompanyRequest $request, \App\Models\Company $company)
{
    // 1) See what we actually receive (sans files) in the logs
    \Log::info('Company UPDATE request (superadmin)', [
        'company_id' => $company->id,
        'payload'    => $request->except(['_token','_method','logo','rib','kbis','id_photo_recto','id_photo_verso','tva_exemption_doc','invoice_terms_doc','signature_path']),
        'route'      => $request->route()?->getName(),
    ]);

    // 2) Build data strictly from the model's fillable keys (works even if FormRequest missed some fields)
    $fillable = $company->getFillable();
    $data = collect($request->all())->only($fillable)->toArray();

    // 3) Handle files (overwrite only if new file uploaded)
    foreach ([
        'logo','rib','kbis','id_photo_recto','id_photo_verso',
        'tva_exemption_doc','invoice_terms_doc','signature_path',
    ] as $fileField) {
        if ($request->hasFile($fileField)) {
            $data[$fileField] = $request->file($fileField)->store('company_files', 'public');
        }
    }

    // 4) Apply + detect changes before saving
    $company->fill($data);
    $changed = $company->getDirty(); // what will change

    if (empty($changed)) {
        return back()->with('error', 'Aucune modification détectée — vérifiez que vous avez bien changé une valeur (route: '
            .($request->route()?->getName() ?? 'n/a').').');
    }

    $company->save();

    // 5) Flash the list of changed keys to help you confirm
    return redirect()
        ->route('superadmin.companies.show', $company)
        ->with('success', 'Société mise à jour.')
        ->with('debug_changed', array_keys($changed));
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