<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\UnitCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitCreditController extends Controller
{

    public function index(Request $request)
{
    $q = \App\Models\UnitCredit::query()->with(['company:id,name,units','author:id,name']);

    if ($request->filled('company_id')) {
        $q->where('company_id', $request->company_id);
    }
    if ($request->filled('source')) {
        $q->where('source', $request->source);
    }
    if ($request->filled('from')) {
        $q->whereDate('created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $q->whereDate('created_at', '<=', $request->to);
    }

    return view('superadmin.units.credits.index', [
        'credits'   => $q->latest()->paginate(20),
        'companies' => \App\Models\Company::orderBy('name')->get(['id','name','units']),
    ]);
}

    public function create()
    {
        return view('superadmin.units.credits.create', [
            'companies' => Company::orderBy('name')->get(['id','name','units']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'units'      => 'required|integer|min:1',
            'note'       => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($data) {
            $company = Company::lockForUpdate()->find($data['company_id']);
            $company->increment('units', (int)$data['units']);

            UnitCredit::create([
                'company_id' => $company->id,
                'created_by' => auth()->id(),
                'units'      => (int)$data['units'],
                'source'     => 'manual',
                'note'       => $data['note'] ?? null,
            ]);
        });

        return redirect()->route('superadmin.units.credits.create')
            ->with('success', 'Unités créditées avec succès.');
    }
}