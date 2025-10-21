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

    public function store(StoreCompanyRequest $request)
    {
        $data = $request->validated();

        // Handle uploads to public disk and set paths into $data
        $this->ingestUploads($request, $data);

        $company = null;

        DB::transaction(function () use (&$company, $data) {
            // Create company with ALL validated + uploaded fields
            $company = Company::create($this->onlyFillable($data));

            // Optionally create first user
            if (!empty($data['create_admin'])) {
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

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $data = $request->validated();

        // Files: upload and set paths
        $this->ingestUploads($request, $data);

        $company->fill($this->onlyFillable($data))->save();

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

    /**
     * Upload known file fields to the public disk and merge back into $data.
     */
    private function ingestUploads($request, array &$data): void
    {
        foreach ([
            'logo', 'rib', 'kbis', 'id_photo_recto', 'id_photo_verso',
            'tva_exemption_doc', 'invoice_terms_doc', 'signature_path',
        ] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('company_files', 'public');
            }
        }
    }

    /**
     * Keep only attributes that are fillable on the Company model.
     */
    private function onlyFillable(array $data): array
    {
        $fillable = (new Company())->getFillable();
        return array_intersect_key($data, array_flip($fillable));
    }
}