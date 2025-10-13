@extends('layout')

@section('content')
<div class="p-6 sm:p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Modifier la facture</h1>
        <a href="{{ route('factures.index') }}" class="text-orange-500 hover:underline">&larr; Retour</a>
    </div>

    <form action="{{ route('factures.update', $facture->id) }}" method="POST" id="factureForm" class="bg-white rounded-xl shadow border border-gray-200 p-6">
        @csrf
        @method('PUT')

        <!-- Infos client -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block mb-1 font-medium text-gray-700">Client</label>
                <select name="client_id" class="w-full border border-gray-300 rounded px-4 py-2">
                    <option value="">-- Sélectionner --</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ $facture->client_id == $client->id ? 'selected' : '' }}>
                            {{ $client->nom_assure }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-medium text-gray-700">Date de la facture</label>
                <input type="date" name="date_facture"
                       value="{{ old('date_facture', $facture->date_facture) }}"
                       class="w-full border border-gray-300 rounded px-4 py-2">
            </div>
        </div>

        <!-- Produits -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Produits / Services</h2>

        <table class="w-full text-sm border rounded shadow-sm mb-6">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-4 py-2">Produit</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Quantité</th>
                    <th class="px-4 py-2">Prix HT</th>
                    <th class="px-4 py-2">TVA (%)</th>
                    <th class="px-4 py-2">Remise (%)</th>
                    <th class="px-4 py-2">Total HT</th>
                </tr>
            </thead>
            <tbody id="itemsTable">
                @foreach($facture->items as $index => $item)
                <tr class="border-t">
                    <td class="px-2 py-2">
                        <select name="items[{{ $index }}][produit]" class="w-full border rounded px-2 py-1">
                            <option value="">-- Produit --</option>
                            @foreach($produits as $produit)
                                <option value="{{ $produit->nom }}" {{ $item->produit == $produit->nom ? 'selected' : '' }}>
                                    {{ $produit->nom }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-2 py-2">
                        <input type="text" name="items[{{ $index }}][description]" value="{{ $item->description }}" class="w-full border rounded px-2 py-1">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" name="items[{{ $index }}][quantite]" value="{{ $item->quantite }}" class="w-full border rounded px-2 py-1">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" step="0.01" name="items[{{ $index }}][prix_unitaire]" value="{{ $item->prix_unitaire }}" class="w-full border rounded px-2 py-1">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" step="0.01" name="items[{{ $index }}][taux_tva]" value="{{ $item->taux_tva }}" class="w-full border rounded px-2 py-1">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" step="0.01" name="items[{{ $index }}][remise]" value="{{ $item->remise }}" class="w-full border rounded px-2 py-1">
                    </td>
                    <td class="px-2 py-2 text-right">{{ number_format($item->total_ht, 2) }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Bloc Modalités & Conditions -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Modalités & conditions de règlement</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block mb-1 font-medium text-gray-700">Mode de paiement</label>
                <input type="text" name="payment_method" value="{{ old('payment_method', $facture->payment_method) }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 font-medium text-gray-700">Date d'échéance</label>
                <input type="date" name="due_date" value="{{ old('due_date', $facture->due_date) }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 font-medium text-gray-700">IBAN</label>
                <input type="text" name="payment_iban" value="{{ old('payment_iban', $facture->payment_iban) }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 font-medium text-gray-700">BIC</label>
                <input type="text" name="payment_bic" value="{{ old('payment_bic', $facture->payment_bic) }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 font-medium text-gray-700">Pénalités retard (%)</label>
                <input type="number" step="0.01" name="penalty_rate" value="{{ old('penalty_rate', $facture->penalty_rate) }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div class="mb-6">
            <label class="block mb-1 font-medium text-gray-700">Texte affiché sur la facture</label>
            <textarea name="payment_terms_text" rows="5" class="w-full border rounded px-3 py-2">{{ old('payment_terms_text', $facture->payment_terms_text) }}</textarea>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded shadow">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection