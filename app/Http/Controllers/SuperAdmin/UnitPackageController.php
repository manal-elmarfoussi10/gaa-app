<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UnitPackage;
use Illuminate\Http\Request;

class UnitPackageController extends Controller
{
    public function index()
    {
        $packages = UnitPackage::orderByDesc('is_active')->orderBy('id')->get();
        $package  = $packages->first();
        $pendingVirements = 0; // fill if you have a counter

        return view('superadmin.units.packages.index', compact('packages','package','pendingVirements'));
    }

    public function create()
    {
        $package = null;
        return view('superadmin.units.packages.create', compact('package'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['nullable','string','max:255'],
            'price_ht'  => ['required','numeric','min:0'],
            'is_active' => ['sometimes','boolean'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? true);
        $data['units']     = 1; // price is per single unit

        // If a pack already exists, update it (single-pack behavior)
        if ($existing = UnitPackage::first()) {
            if ($data['is_active']) {
                UnitPackage::where('id', '!=', $existing->id)->update(['is_active' => false]);
            }
            $existing->update($data);

            return redirect()->route('superadmin.units.packages.index')
                ->with('success', 'Pack mis à jour.');
        }

        $created = UnitPackage::create($data);
        if ($created->is_active) {
            UnitPackage::where('id', '!=', $created->id)->update(['is_active' => false]);
        }

        return redirect()->route('superadmin.units.packages.index')
            ->with('success', 'Pack créé.');
    }

    public function edit(UnitPackage $unit_package)
    {
        $package = $unit_package;
        return view('superadmin.units.packages.edit', compact('package'));
    }

    public function update(Request $request, UnitPackage $unit_package)
    {
        $data = $request->validate([
            'name'      => ['nullable','string','max:255'],
            'price_ht'  => ['required','numeric','min:0'],
            'is_active' => ['sometimes','boolean'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? false);
        $data['units']     = 1;

        $unit_package->update($data);

        if ($unit_package->is_active) {
            UnitPackage::where('id','!=',$unit_package->id)->update(['is_active' => false]);
        }

        return redirect()->route('superadmin.units.packages.index')
            ->with('success', 'Pack mis à jour.');
    }

    // "delete" = deactivate current pack
    public function destroy(UnitPackage $unit_package)
    {
        $unit_package->update(['is_active' => false]);

        return redirect()->route('superadmin.units.packages.index')
            ->with('success', 'Pack désactivé.');
    }
}