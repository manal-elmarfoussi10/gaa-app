<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\VirementRequest;
use App\Models\UnitCredit;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    $request->validate([
        'credit_units' => 'required|integer|min:1',
        'notes'        => 'nullable|string|max:2000',
    ]);

    DB::transaction(function () use ($request, $virement) {
        $company = Company::findOrFail($virement->company_id);

        // 1) increase the live counter on companies
        $company->increment('units', (int) $request->credit_units);

        // 2) mark virement + keep a ledger line
        $virement->update([
            'status' => 'approved',
            'notes'  => $request->notes,
        ]);

        UnitCredit::create([
            'company_id'          => $company->id,
            'created_by'          => auth()->id(),
            'units'               => (int) $request->credit_units,
            'source'              => 'virement',
            'virement_request_id' => $virement->id,
            'note'                => $request->notes,
        ]);
    });

    return back()->with('success', 'Virement approuvé et unités créditées.');
}

    public function reject(Request $request, VirementRequest $virement)
    {
        $data = $request->validate(['notes'=>'nullable|string|max:1000']);
        $virement->update([
            'status' => 'rejected',
            'notes'  => $data['notes'] ?? null,
        ]);

        return back()->with('success','Virement rejeté.');
    }

    public function downloadProof(VirementRequest $virement)
    {
        abort_unless($virement->proof_path && Storage::disk('public')->exists($virement->proof_path), 404);
        return Storage::disk('public')->download($virement->proof_path);
    }
}