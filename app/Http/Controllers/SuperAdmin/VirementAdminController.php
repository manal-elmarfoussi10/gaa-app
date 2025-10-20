<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\VirementRequest;
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

    public function show(VirementRequest $virement)
    {
        $virement->load(['company','user']);
        return view('superadmin.units.virements.show', compact('virement'));
    }

    public function approve(Request $request, VirementRequest $virement)
    {
        $data = $request->validate([
            'credit_units' => 'required|integer|min:1',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $company = $virement->company;
        abort_if(!$company, 404);

        $company->increment('units', $data['credit_units']);

        $virement->update([
            'status' => 'approved',
            'notes'  => $data['notes'] ?? null,
        ]);

        return redirect()->route('superadmin.virements.index')->with('success','Virement approuvé et unités créditées.');
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