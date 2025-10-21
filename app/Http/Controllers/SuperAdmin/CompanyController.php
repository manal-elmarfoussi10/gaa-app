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
    // Log incoming (no files)
    \Log::info('Company STORE request (superadmin)', [
        'payload' => $request->except(['_token','logo','rib','kbis','id_photo_recto','id_photo_verso','tva_exemption_doc','invoice_terms_doc','signature_path']),
        'route'   => $request->route()?->getName(),
    ]);

    // Build from the Company fillable
    $tmpCompany = new \App\Models\Company();
    $fillable   = $tmpCompany->getFillable();
    $data       = collect($request->all())->only($fillable)->toArray();

    foreach ([
        'logo','rib','kbis','id_photo_recto','id_photo_verso',
        'tva_exemption_doc','invoice_terms_doc','signature_path',
    ] as $fileField) {
        if ($request->hasFile($fileField)) {
            $data[$fileField] = $request->file($fileField)->store('company_files', 'public');
        }
    }

    $company = null;

    \DB::transaction(function () use (&$company, $data, $request) {
        $company = \App\Models\Company::create($data);

        // Optional embedded admin creation
        if ($request->boolean('create_admin') && $request->filled('admin.email')) {
            $a = $request->input('admin');
            $user = new \App\Models\User();
            $user->first_name = $a['first_name'] ?? '';
            $user->last_name  = $a['last_name']  ?? '';
            $user->name       = trim(($a['first_name'] ?? '').' '.($a['last_name'] ?? ''));
            $user->email      = $a['email'];
            $user->role       = $a['role'] ?? \App\Models\User::ROLE_ADMIN;
            $user->company_id = $company->id;
            $user->is_active  = isset($a['is_active']) ? (bool)$a['is_active'] : true;
            $user->password   = \Illuminate\Support\Facades\Hash::make($a['password']);
            $user->save();
        }
    });

    return redirect()
        ->route('superadmin.companies.show', $company)
        ->with('success','Société créée avec succès.');
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