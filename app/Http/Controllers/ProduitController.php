<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('categorie', 'like', "%{$search}%");
            });
        }

        $produits = $query->get();

        return view('produits.index', compact('produits'));
    }

    public function create()
    {
        return view('produits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'code'        => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'prix_ht'     => 'required|numeric|min:0',
            // on accepte vide mais numérique si rempli
            'montant_tva' => 'nullable|numeric|min:0',
            'categorie'   => 'nullable|string|max:255',
            'actif'       => 'nullable|boolean',
        ]);

        // Checkbox actif
        $validated['actif'] = $request->has('actif');

        // ⚠️ IMPORTANT : ne jamais envoyer NULL à MySQL
        $validated['montant_tva'] = $request->filled('montant_tva')
            ? $request->input('montant_tva')
            : 0; // valeur par défaut

        Produit::create($validated);

        return redirect()
            ->route('produits.index')
            ->with('success', 'Produit ajouté avec succès.');
    }

    public function edit(Produit $produit)
    {
        return view('produits.edit', compact('produit'));
    }

    public function update(Request $request, Produit $produit)
    {
        // Forcer la valeur de 'actif' avant validation
        $request->merge([
            'actif' => $request->has('actif') ? 1 : 0,
        ]);

        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'code'        => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'prix_ht'     => 'required|numeric|min:0',
            // idem ici : nullable mais jamais NULL dans la BDD
            'montant_tva' => 'nullable|numeric|min:0',
            'categorie'   => 'nullable|string|max:255',
            'actif'       => 'boolean',
        ]);

        // valeur par défaut si vide
        $validated['montant_tva'] = $request->filled('montant_tva')
            ? $request->input('montant_tva')
            : 0;

        $produit->update($validated);

        return redirect()
            ->route('produits.index')
            ->with('success', 'Produit mis à jour.');
    }

    public function destroy(Produit $produit)
    {
        $produit->delete();

        return redirect()
            ->route('produits.index')
            ->with('success', 'Produit supprimé.');
    }
}
