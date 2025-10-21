<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;           // ⬅️ add this
use App\Models\Company;                      // ⬅️ and this (if you use it)
use App\Models\VirementRequest;
use App\Models\UnitCredit;  

class VirementAdminController extends Controller
{
    public function index()
    {
        $states = ['pending','approved','rejected'];
        $current = request('status','pending');
        $requests = VirementRequest::with(['company','user'])
            ->when($current, fn($q) => $q->where('status',$current))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('superadmin.units.virements.index', compact('requests','states','current'));
    }

    public function show(\App\Models\VirementRequest $virement)
    {
        $states = ['pending','approved','rejected'];
    
        $proofUrl = $virement->proof_path
            ? route('attachment', ['path' => $virement->proof_path])
            : null;
    
        return view('superadmin.units.virements.show', [
            'virement'  => $virement,
            'states'    => $states,
            'proofUrl'  => $proofUrl,
        ]);
    }

    public function approve(Request $request, VirementRequest $virement)
{
    if ($virement->status !== 'pending') {
        return back()->with('error', 'Cette demande a déjà été traitée.');
    }

    $data = $request->validate([
        'credit_units' => ['required','integer','min:1'],
        'notes'        => ['nullable','string','max:2000'],
    ]);

    DB::transaction(function () use ($virement, $data, $request) {
        // 1) credit company units
        $company = Company::findOrFail($virement->company_id);
        $company->increment('units', (int) $data['credit_units']);

        // 2) optional: keep a ledger entry if your table exists
        if (class_exists(UnitCredit::class)) {
            UnitCredit::create([
                'company_id'          => $company->id,
                'created_by'          => $request->user()->id,
                'units'               => (int) $data['credit_units'],
                'source'              => 'virement',
                'virement_request_id' => $virement->id,
                'note'                => $data['notes'] ?? null,
            ]);
        }

        // 3) mark request approved
        $virement->update([
            'status' => 'approved',
            'notes'  => $data['notes'] ?? $virement->notes,
        ]);
    });

    return back()->with('success', 'Unité(s) créditée(s) et demande approuvée.');
}

public function reject(Request $request, VirementRequest $virement)
{
    if ($virement->status !== 'pending') {
        return back()->with('error', 'Cette demande a déjà été traitée.');
    }

    $data = $request->validate([
        'notes' => ['nullable','string','max:2000'],
    ]);

    $virement->update([
        'status' => 'rejected',
        'notes'  => $data['notes'] ?? $virement->notes,
    ]);

    return back()->with('success', 'Demande rejetée.');
}

    public function downloadProof(VirementRequest $virement)
    {
        abort_unless($virement->proof_path && Storage::disk('public')->exists($virement->proof_path), 404);
        return Storage::disk('public')->download($virement->proof_path);
    }
}