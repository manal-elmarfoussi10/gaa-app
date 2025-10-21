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
            'name'     => 'nullable|string|max:255',
            'units'    => 'nullable|integer|min:1',
            'price_ht' => 'required|numeric|min:0',
            'is_active'=> 'sometimes|boolean',
        ]);
    
        // normalize checkbox
        $data['is_active'] = $request->boolean('is_active');
    
        if ($data['is_active']) {
            UnitPackage::query()->update(['is_active' => false]);
        }
        UnitPackage::create($data);
    
        return redirect()
            ->route('superadmin.units.packages.index')
            ->with('success', $data['is_active'] ? 'Pack activé.' : 'Pack créé.');
    }

    public function edit(UnitPackage $unit_package)
    {
        $package = $unit_package;
        return view('superadmin.units.packages.edit', compact('package'));
    }

    public function update(Request $request, UnitPackage $unit_package)
{
    $data = $request->validate([
        'name'     => 'nullable|string|max:255',
        'units'    => 'nullable|integer|min:1',
        'price_ht' => 'required|numeric|min:0',
        'is_active'=> 'sometimes|boolean',
    ]);

    $data['is_active'] = $request->boolean('is_active');

    if ($data['is_active']) {
        UnitPackage::query()->where('id', '!=', $unit_package->id)->update(['is_active' => false]);
    }

    $unit_package->update($data);

    return redirect()
        ->route('superadmin.units.packages.index')
        ->with('success', $data['is_active'] ? 'Pack activé.' : 'Pack mis à jour.');
}

    // "delete" = deactivate current pack
    public function destroy(UnitPackage $unit_package)
    {
        $unit_package->update(['is_active' => false]);

        return redirect()->route('superadmin.units.packages.index')
            ->with('success', 'Pack désactivé.');
    }

    public function activate(UnitPackage $unit_package)
{
    UnitPackage::query()->update(['is_active' => false]);
    $unit_package->forceFill(['is_active' => true])->save();

    return redirect()
        ->route('superadmin.units.packages.index')
        ->with('success', 'Pack activé.');
}
}