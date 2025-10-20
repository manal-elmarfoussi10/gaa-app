<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UnitPackage;
use Illuminate\Http\Request;

class UnitPackageController extends Controller
{
    public function index()
    {
        // Get the single row (or null)
        $package = UnitPackage::first();

        return view('superadmin.units.packages.form', [
            'package' => $package,
        ]);
    }

    public function create()
    {
        // We don’t create multiple; just send to index
        return redirect()->route('superadmin.units.packages.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'nullable|string|max:100', // optional label
            'price_per_unit' => 'required|numeric|min:0',
            'tax_rate'       => 'required|numeric|min:0|max:100',
            'is_active'      => 'sometimes|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        // Ensure single row: update existing or create one
        $package = UnitPackage::first();
        if ($package) {
            $package->update($data);
        } else {
            // force ID=1 if you want, otherwise just create()
            $package = UnitPackage::create($data + ['id' => 1]);
        }

        return redirect()
            ->route('superadmin.units.packages.index')
            ->with('success', 'Paramètres des unités enregistrés.');
    }

    public function edit(UnitPackage $unit_package)
    {
        // Not used – keep everything on index
        return redirect()->route('superadmin.units.packages.index');
    }

    public function update(Request $request, UnitPackage $unit_package)
    {
        // Also unused if you only post to store(); kept for completeness
        $data = $request->validate([
            'name'           => 'nullable|string|max:100',
            'price_per_unit' => 'required|numeric|min:0',
            'tax_rate'       => 'required|numeric|min:0|max:100',
            'is_active'      => 'sometimes|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $unit_package->update($data);

        return redirect()
            ->route('superadmin.units.packages.index')
            ->with('success', 'Paramètres des unités mis à jour.');
    }

    public function destroy(UnitPackage $unit_package)
    {
        // Prefer disabling instead of deleting the only row
        $unit_package->update(['is_active' => false]);

        return redirect()
            ->route('superadmin.units.packages.index')
            ->with('success', 'Pack désactivé.');
    }
}