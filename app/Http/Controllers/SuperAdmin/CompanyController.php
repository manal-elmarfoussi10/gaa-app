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
        // roles list (no superadmin here)
        $roles = collect(User::roles())->except(User::ROLE_SUPERADMIN)->toArray();
        return view('superadmin.companies.create', compact('roles'));
    }

    public function store(StoreCompanyRequest $request)
    {
        $data = $request->validated();
        $company = null;

        DB::transaction(function () use ($data, &$company) {
            // 1) create company
            $company = Company::create([
                'name'  => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            // 2) optionally create first user
            if (!empty($data['create_admin'])) {
                $a = $data['admin']; // validated nested payload

                $user = new User();
                $user->first_name = $a['first_name'];
                $user->last_name  = $a['last_name'];
                $user->name       = trim($a['first_name'].' '.$a['last_name']);
                $user->email      = $a['email'];
                $user->role       = $a['role'];                 // e.g. 'admin'
                $user->company_id = $company->id;
                $user->is_active  = isset($a['is_active']) ? (bool) $a['is_active'] : true;

                // password is already validated + confirmed; now hash it
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
        $company->fill($request->validated())->save();

        return redirect()
            ->route('superadmin.companies.show', $company)
            ->with('success', 'Société mise à jour.');
    }

    public function destroy(Company $company)
    {
        // Optional cascade
        $company->users()->delete();
        $company->delete();

        return redirect()
            ->route('superadmin.companies.index')
            ->with('success', 'Société supprimée avec succès.');
    }
}