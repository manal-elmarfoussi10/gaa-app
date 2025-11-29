@extends('layout')

@section('content')
<div class="p-4 sm:p-6">
    <style>
        input[type=number].no-spinners::-webkit-outer-spin-button,
        input[type=number].no-spinners::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number].no-spinners { -moz-appearance: textfield; appearance: textfield; }
    </style>

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 p-6 bg-white rounded-xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Modifier la facture</h1>
            <p class="text-gray-600 mt-1">Facture n° <span class="font-semibold">{{ $facture->numero }}</span></p>
        </div>
        <a href="{{ route('factures.index') }}" class="flex items-center gap-2 text-orange-600 hover:text-orange-800">
            <!-- back -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Retour à la liste
        </a>
    </div>

    <form action="{{ route('factures.update', $facture->id) }}" method="POST" id="factureForm"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @csrf
        @method('PUT')

        @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="text-red-800 font-medium mb-2">Erreurs de validation :</h3>
            <ul class="list-disc pl-5 text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Infos client -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Informations client</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Client (optionnel si prospect)</label>
                    <select id="client_id" name="client_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50 appearance-none">
                        <option value="">— Aucun (prospect) —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_id', $facture->client_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->nom_assure }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Si vous laissez vide, la facture reste liée à un “prospect”.</p>
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Date de facture <span class="text-red-500">*</span></label>
                    <input type="date" name="date_facture"
                           value="{{ old('date_facture', optional($facture->date_facture)->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                           required>
                </div>
            </div>

            {{-- Champs prospect (si facture sans client) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Nom du prospect</label>
                    <input type="text" name="prospect_name"
                           value="{{ old('prospect_name', $facture->prospect_name) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Email</label>
                    <input type="email" name="prospect_email"
                           value="{{ old('prospect_email', $facture->prospect_email) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Téléphone</label>
                    <input type="text" name="prospect_phone"
                           value="{{ old('prospect_phone', $facture->prospect_phone) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Adresse</label>
                    <textarea name="prospect_address" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Adresse complète du prospect">{{ old('prospect_address', $facture->prospect_address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Produits / Services -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800">Produits / Services</h2>
                <button type="button" id="addProductBtn"
                        class="flex items-center gap-1 text-sm bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-1.5 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Ajouter un produit
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-600">Produit</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Description</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Quantité</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Prix HT (€)</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Taux TVA (%)</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Remise (%)</th>
                            <th class="px-4 py-3 font-medium text-gray-600 text-right">Total HT</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTable">
                        @php $rowId = 0; @endphp
                        @foreach($facture->items as $it)
                            <tr class="border-b">
                                <td class="px-4 py-3">
                                    <input type="text" name="items[{{ $rowId }}][produit]" value="{{ old("items.$rowId.produit", $it->produit) }}" required
                                           class="w-full border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="items[{{ $rowId }}][description]" value="{{ old("items.$rowId.description", $it->description) }}"
                                           class="w-full border border-gray-300 rounded px-3 py-2">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" inputmode="decimal" min="0" required
                                           name="items[{{ $rowId }}][quantite]" value="{{ old("items.$rowId.quantite", $it->quantite) }}"
                                           class="no-spinners w-20 border border-gray-300 rounded px-3 py-2">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" inputmode="decimal" min="0" required
                                           name="items[{{ $rowId }}][prix_unitaire]" value="{{ old("items.$rowId.prix_unitaire", $it->prix_unitaire) }}"
                                           class="no-spinners w-24 border border-gray-300 rounded px-3 py-2">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" inputmode="decimal" min="0" required
                                           name="items[{{ $rowId }}][taux_tva]" value="{{ old("items.$rowId.taux_tva", $it->taux_tva) }}"
                                           class="no-spinners w-20 border border-gray-300 rounded px-3 py-2">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" inputmode="decimal" min="0" max="100"
                                           name="items[{{ $rowId }}][remise]" value="{{ old("items.$rowId.remise", $it->remise) }}"
                                           class="no-spinners w-20 border border-gray-300 rounded px-3 py-2">
                                </td>
                                <td class="px-4 py-3 text-right font-medium"><span class="total-cell">0.00 €</span></td>
                                <td class="px-4 py-3">
                                    <button type="button" class="delete-row-btn text-red-500 hover:text-red-700" title="Supprimer la ligne">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            @php $rowId++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Récap -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Récapitulatif</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="font-medium text-gray-700 mb-2">Total HT</h3>
                    <p class="text-2xl font-bold text-gray-800" id="total-ht">0.00 €</p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="font-medium text-gray-700 mb-2">Total TVA</h3>
                    <p class="text-2xl font-bold text-gray-800" id="tva">0.00 €</p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200 bg-orange-50 border-orange-200">
                    <h3 class="font-medium text-orange-700 mb-2">Total TTC</h3>
                    <p class="text-2xl font-bold text-orange-700" id="total-ttc">0.00 €</p>
                </div>
            </div>
        </div>

        {{-- Modalités & conditions de règlement --}}
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800">Modalités & conditions de règlement</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Mode de paiement</label>
                    <input type="text" name="payment_method"
                           value="{{ old('payment_method', $facture->payment_method) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Date d'échéance</label>
                    <input type="date" name="due_date"
                           value="{{ old('due_date', $facture->due_date ? \Carbon\Carbon::parse($facture->due_date)->toDateString() : '') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">IBAN</label>
                    <input type="text" name="payment_iban"
                           value="{{ old('payment_iban', $facture->payment_iban) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">BIC</label>
                    <input type="text" name="payment_bic"
                           value="{{ old('payment_bic', $facture->payment_bic) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Taux des pénalités de retard (%)</label>
                    <input type="number" step="0.01" inputmode="decimal" name="penalty_rate"
                           value="{{ old('penalty_rate', $facture->penalty_rate) }}"
                           class="no-spinners w-full border border-gray-300 rounded-lg px-4 py-3">
                </div>
            </div>

            <div class="mt-6">
                <label class="block mb-2 font-medium text-gray-700">Texte affiché sur la facture</label>
                <textarea name="payment_terms_text" rows="6"
                          class="w-full border border-gray-300 rounded-lg px-4 py-3">{{ old('payment_terms_text', $facture->payment_terms_text) }}</textarea>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-4 pt-4 border-t border-gray-100">
            <a href="{{ route('factures.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Annuler</a>
            <button type="submit" id="submitBtn"
                    class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-6 py-3 rounded-lg transition-all shadow-md hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Mettre à jour
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (sel, scope=document) => scope.querySelector(sel);
    const $$ = (sel, scope=document) => Array.from(scope.querySelectorAll(sel));

    // number normalizer
    function normalize(v){ return (''+v).replace(/\s/g,'').replace(',', '.'); }
    function recalc(){
        let totalHT=0, totalTVA=0;
        $$('#itemsTable tr').forEach(row=>{
            const q = parseFloat(normalize(row.querySelector('input[name*="[quantite]"]').value)) || 0;
            const p = parseFloat(normalize(row.querySelector('input[name*="[prix_unitaire]"]').value)) || 0;
            const t = parseFloat(normalize(row.querySelector('input[name*="[taux_tva]"]').value)) || 0;
            const d = parseFloat(normalize(row.querySelector('input[name*="[remise]"]').value)) || 0;
            let ht = q*p; if(d>0) ht -= ht*d/100;
            const tva = ht*(t/100);
            row.querySelector('.total-cell').textContent = ht.toFixed(2)+' €';
            totalHT += ht; totalTVA += tva;
        });
        $('#total-ht').textContent = totalHT.toFixed(2)+' €';
        $('#tva').textContent     = totalTVA.toFixed(2)+' €';
        $('#total-ttc').textContent= (totalHT+totalTVA).toFixed(2)+' €';
    }

    // attach handlers
    function attach(el){
        el.querySelectorAll('input').forEach(i=>{
            i.addEventListener('input', recalc);
            if(i.type==='number'){
                i.addEventListener('blur', ()=>{ i.value = normalize(i.value); });
            }
        });
        el.querySelector('.delete-row-btn')?.addEventListener('click', ()=>{ el.remove(); recalc(); });
    }
    $$('#itemsTable tr').forEach(attach);

    // add new row
    let rowId = {{ max(0, $facture->items->count()) }};
    $('#addProductBtn').addEventListener('click', ()=>{
        const tr = document.createElement('tr');
        tr.className='border-b';
        tr.innerHTML = `
            <td class="px-4 py-3"><input name="items[${rowId}][produit]" required class="w-full border border-gray-300 rounded px-3 py-2"></td>
            <td class="px-4 py-3"><input name="items[${rowId}][description]" class="w-full border border-gray-300 rounded px-3 py-2"></td>
            <td class="px-4 py-3"><input type="number" step="0.01" inputmode="decimal" min="0" value="1" required name="items[${rowId}][quantite]" class="no-spinners w-20 border border-gray-300 rounded px-3 py-2"></td>
            <td class="px-4 py-3"><input type="number" step="0.01" inputmode="decimal" min="0" value="0" required name="items[${rowId}][prix_unitaire]" class="no-spinners w-24 border border-gray-300 rounded px-3 py-2"></td>
            <td class="px-4 py-3"><input type="number" step="0.01" inputmode="decimal" min="0" value="20" required name="items[${rowId}][taux_tva]" class="no-spinners w-20 border border-gray-300 rounded px-3 py-2"></td>
            <td class="px-4 py-3"><input type="number" step="0.01" inputmode="decimal" min="0" max="100" value="0" name="items[${rowId}][remise]" class="no-spinners w-20 border border-gray-300 rounded px-3 py-2"></td>
            <td class="px-4 py-3 text-right font-medium"><span class="total-cell">0.00 €</span></td>
            <td class="px-4 py-3"><button type="button" class="delete-row-btn text-red-500 hover:text-red-700">Supprimer</button></td>`;
        document.querySelector('#itemsTable').appendChild(tr);
        attach(tr);
        rowId++;
        recalc();
    });

    // initial totals
    recalc();

    // basic submit validation
    document.getElementById('factureForm').addEventListener('submit', function(e){
        if(!document.querySelectorAll('#itemsTable tr').length){
            e.preventDefault(); alert('Veuillez ajouter au moins un produit'); return;
        }
    });
});
</script>
@endsection