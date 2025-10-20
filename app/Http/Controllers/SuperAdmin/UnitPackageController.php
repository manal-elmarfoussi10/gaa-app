<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UnitPackage;
use Illuminate\Http\Request;

class UnitPackageController extends Controller
{
    public function index()
    {
        $packages = UnitPackage::orderBy('units')->get();
        return view('superadmin.units.packages.index', compact('packages'));
    }

    public function create()
    {
        $package = new UnitPackage(['is_active' => true]);
        return view('superadmin.units.packages.form', compact('package'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'units'     => 'required|integer|min:1',
            'price_ht'  => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        UnitPackage::create($data);
        return redirect()->route('superadmin.unit-packages.index')->with('success','Pack créé.');
    }

    public function edit(UnitPackage $unit_package)
    {
        return view('superadmin.units.packages.form', ['package' => $unit_package]);
    }

    public function update(Request $request, UnitPackage $unit_package)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'units'     => 'required|integer|min:1',
            'price_ht'  => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        $data['is_active'] = (bool)($data['is_active'] ?? false);

        $unit_package->update($data);
        return redirect()->route('superadmin.unit-packages.index')->with('success','Pack mis à jour.');
    }

    public function destroy(UnitPackage $unit_package)
    {
        $unit_package->delete();
        return back()->with('success','Pack supprimé.');
    }
}