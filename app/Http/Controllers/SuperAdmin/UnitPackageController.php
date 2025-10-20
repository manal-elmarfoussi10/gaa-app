<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UnitPackage;
use Illuminate\Http\Request;

class UnitPackageController extends Controller
{
    // One simple list page + “configure” action
    public function index()
    {
        $packages = UnitPackage::orderByDesc('is_active')->orderBy('id')->get();

        // If you want a single “settings” page instead of a list:
        $package = UnitPackage::orderBy('id')->first();

        return view('superadmin.units.packages.index', compact('packages', 'package'));
    }

    // We’ll use the same form for create/update
    public function create()
    {
        $package = null;
        return view('superadmin.units.packages.form', compact('package'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['nullable', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'vat_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        // Ensure single pack behavior: if there is already a pack, update it
        $existing = UnitPackage::first();
        if ($existing) {
            // Deactivate other packs if this one is active
            if ($data['is_active']) {
                UnitPackage::where('id', '!=', $existing->id)->update(['is_active' => false]);
            }
            $existing->update($data);

            return redirect()
                ->route('superadmin.units.packages.index')
                ->with('success', 'Pack mis à jour.');
        }

        // Create the first pack
        $created = UnitPackage::create($data);
        if ($created->is_active) {
            UnitPackage::where('id', '!=', $created->id)->update(['is_active' => false]);
        }

        return redirect()
            ->route('superadmin.units.packages.index')
            ->with('success', 'Pack créé.');
    }

    public function edit(UnitPackage $unit_package)
    {
        $package = $unit_package;
        return view('superadmin.units.packages.form', compact('package'));
    }

    public function update(Request $request, UnitPackage $unit_package)
    {
        $data = $request->validate([
            'name'       => ['nullable', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'vat_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $unit_package->update($data);

        // If we activate this pack, deactivate all others.
        if ($unit_package->is_active) {
            UnitPackage::where('id', '!=', $unit_package->id)->update(['is_active' => false]);
        }

        return redirect()
            ->route('superadmin.units.packages.index')
            ->with('success', 'Pack mis à jour.');
    }

    // “Delete” = deactivate (we keep the record)
    public function destroy(UnitPackage $unit_package)
    {
        $unit_package->update(['is_active' => false]);

        return redirect()
            ->route('superadmin.units.packages.index')
            ->with('success', 'Pack désactivé.');
    }
}