@extends('layout')

@section('content')
<div class="p-4 sm:p-6">
  <style>
    /* Hide number spinners on all browsers */
    input.no-spinners::-webkit-outer-spin-button,
    input.no-spinners::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input.no-spinners { -moz-appearance: textfield; appearance: textfield; }

    /* Tiny helper to make readonly “chips” nice */
    .chip{display:inline-flex;align-items:center;gap:.5rem;background:#F8FAFC;border:1px solid #E5E7EB;border-radius:9999px;padding:.35rem .7rem;font-size:.875rem}
  </style>

  <!-- Header -->
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 p-6 bg-white rounded-xl shadow-sm border border-gray-100">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Créer un nouveau devis</h1>
      <p class="text-gray-600 mt-1">Sélectionnez des produits, ajoutez-en, et choisissez le destinataire</p>
    </div>
    <a href="{{ route('devis.index') }}" class="flex items-center gap-2 text-orange-600 hover:text-orange-800">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
      Retour à la liste
    </a>
  </div>

  <form action="{{ route('devis.store') }}" method="POST" id="devisForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    @csrf

    @if ($errors->any())
      <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
        <h3 class="text-red-800 font-medium mb-2">Erreurs de validation :</h3>
        <ul class="list-disc pl-5 text-red-700">
          @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif

    <!-- Destinataire -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Informations client</h2>

      <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Destinataire du devis</h2>

      <!-- Mode selector -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <label class="border rounded-lg p-3 flex items-start gap-3 cursor-pointer has-[:checked]:border-orange-400">
          <input type="radio" name="client_mode" value="existing" class="mt-1"
                 {{ old('client_mode','existing')==='existing'?'checked':'' }}>
          <div><div class="font-medium">Client existant</div>
               <div class="text-sm text-gray-600">Rechercher dans vos clients</div></div>
        </label>

        <label class="border rounded-lg p-3 flex items-start gap-3 cursor-pointer has-[:checked]:border-orange-400">
          <input type="radio" name="client_mode" value="prospect" class="mt-1"
                 {{ old('client_mode')==='prospect'?'checked':'' }}>
          <div><div class="font-medium">Prospect</div>
               <div class="text-sm text-gray-600">Sans créer de fiche client</div></div>
        </label>
      </div>

      <!-- Client existant -->
      <div id="section-existing" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
        <div>
          <label class="block mb-2 font-medium text-gray-700">Client</label>
          <div class="relative">
            <select id="client_id" name="client_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
              <option value="">-- Sélectionner un client --</option>
              @foreach($clients as $client)
                <option value="{{ $client->id }}"
                        data-km="{{ $client->kilometrage ?? '' }}"
                        {{ old('client_id') == $client->id ? 'selected' : '' }}>
                  {{ $client->nom_assure }} @if($client->plaque) — {{ $client->plaque }} @endif
                </option>
              @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
              <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
              </svg>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-1">Astuce : tapez pour filtrer (selon votre navigateur)</p>

          <!-- Kilometrage preview -->
          <div id="kmPreview" class="mt-3 hidden">
            <span class="chip">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="currentColor"><path d="M6 3a1 1 0 00-1 1v2h2V5h10v1h2V4a1 1 0 00-1-1H6z"/><path d="M5 8h14v10a2 2 0 01-2 2H7a2 2 0 01-2-2V8zm4 3v6h2v-6H9zm4 0v6h2v-6h-2z"/></svg>
              <strong>Kilométrage :</strong>
              <span id="kmValue" class="font-medium"></span>
            </span>
          </div>
        </div>
      </div>

      <!-- Prospect -->
      <div id="section-prospect" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 hidden">
        <div>
          <label class="block mb-2 font-medium text-gray-700">Nom du prospect *</label>
          <input type="text" name="prospect_name" value="{{ old('prospect_name') }}"
                 class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                 placeholder="Ex: Entreprise Martin">
        </div>
        <div>
          <label class="block mb-2 font-medium text-gray-700">Email</label>
          <input type="email" name="prospect_email" value="{{ old('prospect_email') }}"
                 class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
        </div>
        <div>
          <label class="block mb-2 font-medium text-gray-700">Téléphone</label>
          <input type="text" name="prospect_phone" value="{{ old('prospect_phone') }}"
                 class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
        </div>
        <div>
          <label class="block mb-2 font-medium text-gray-700">Adresse</label>
          <textarea name="prospect_address" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                    placeholder="Adresse complète du prospect">{{ old('prospect_address') }}</textarea>
        </div>
      </div>
    </div>

    <!-- Dates & infos -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Dates</h2>

      <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Informations du devis</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block mb-2 font-medium text-gray-700">Titre du devis</label>
          <input type="text" name="titre" value="{{ old('titre') }}"
                 class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                 placeholder="Ex: Remplacement pare-brise">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block mb-2 font-medium text-gray-700">Date du devis *</label>
            <input type="date" name="date_devis" value="{{ old('date_devis', date('Y-m-d')) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block mb-2 font-medium text-gray-700">Valide jusqu'au *</label>
            <input type="date" name="date_validite" value="{{ old('date_validite', date('Y-m-d', strtotime('+30 days'))) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
          </div>
        </div>
      </div>
    </div>

    <!-- Produits / services -->
    <div class="mb-8">
      <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
        <h2 class="text-xl font-semibold text-gray-800">Produits / Services</h2>
        <div class="flex gap-2">
          <button type="button" id="addProductBtn" class="flex items-center gap-1 text-sm bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-1.5 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
            Ajouter un produit
          </button>
          <button type="button" id="addFromCatalogBtn" class="flex items-center gap-1 text-sm bg-green-50 text-green-700 hover:bg-green-100 px-3 py-1.5 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>
            Catalogue
          </button>
        </div>
      </div>

      <!-- Modal catalogue (unchanged except data-tva key) -->
      <div id="productCatalogModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl max-h-[80vh] overflow-hidden flex flex-col">
          <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Sélectionner des produits</h3>
            <button type="button" id="closeCatalogBtn" class="text-gray-500 hover:text-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-4 bg-gray-50 border-b">
            <div class="flex gap-2">
              <input type="text" id="catalogSearch" placeholder="Rechercher des produits..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
              <button type="button" id="addSelectedBtn" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">Ajouter sélection</button>
            </div>
          </div>
          <div class="overflow-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-100 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left"><input type="checkbox" id="selectAllProduits"></th>
                <th class="px-4 py-3 text-left">Produit</th>
                <th class="px-4 py-3 text-left">Description</th>
                <th class="px-4 py-3 text-left">Code</th>
                <th class="px-4 py-3 text-left">Prix HT</th>
                <th class="px-4 py-3 text-left">Taux TVA (%)</th>
              </tr>
              </thead>
              <tbody id="catalogTable" class="divide-y divide-gray-200">
                @foreach($produits as $produit)
                  <tr>
                    <td class="px-4 py-3">
                      <input type="checkbox" class="produit-checkbox"
                             data-id="{{ $produit->id }}"
                             data-name="{{ $produit->nom }}"
                             data-description="{{ $produit->description }}"
                             data-price="{{ $produit->prix_ht }}"
                             data-tva="{{ $produit->taux_tva }}">
                    </td>
                    <td class="px-4 py-3 font-medium">{{ $produit->nom }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $produit->description }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $produit->code }}</td>
                    <td class="px-4 py-3 font-medium">{{ number_format($produit->prix_ht, 2) }} €</td>
                    <td class="px-4 py-3 font-medium">{{ number_format($produit->taux_tva, 2) }}%</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Items table -->
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
          <tbody id="itemsTable"></tbody>
        </table>
      </div>
    </div>

    <!-- Recap -->
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

    <!-- Submit -->
    <div class="flex justify-end gap-4 pt-4 border-t border-gray-100">
      <a href="{{ route('devis.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Annuler</a>
      <button type="submit" id="submitBtn"
              class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-6 py-3 rounded-lg transition-all shadow-md hover:shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        Créer le devis
      </button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let rowCount = 0;

  // Toggle sections
  const radios = document.querySelectorAll('input[name="client_mode"]');
  const secExisting = document.getElementById('section-existing');
  const secProspect = document.getElementById('section-prospect');

  function syncSections() {
    const mode = document.querySelector('input[name="client_mode"]:checked')?.value || 'existing';
    secExisting.classList.toggle('hidden', mode !== 'existing');
    secProspect.classList.toggle('hidden', mode !== 'prospect');
  }
  radios.forEach(r => r.addEventListener('change', syncSections));
  syncSections();

  // Kilometrage chip
  const clientSelect = document.getElementById('client_id');
  const kmWrap  = document.getElementById('kmPreview');
  const kmValue = document.getElementById('kmValue');
  function updateKm() {
    const opt = clientSelect.options[clientSelect.selectedIndex];
    const km  = opt?.getAttribute('data-km') || '';
    if (km) { kmValue.textContent = km + ' km'; kmWrap.classList.remove('hidden'); }
    else    { kmWrap.classList.add('hidden'); kmValue.textContent = ''; }
  }
  clientSelect?.addEventListener('change', updateKm);
  updateKm();

  // Utility: normalize comma to dot
  function toNumber(val) {
    if (typeof val !== 'string') return Number(val) || 0;
    return parseFloat(val.replace(',', '.')) || 0;
  }

  function addRow(produit=null) {
    const rowId = rowCount++;
    const name = produit?.name || '';
    const desc = produit?.description || '';
    const price= produit?.price || '0';
    const tva  = produit?.tva   || '20.00';

    const tr = document.createElement('tr');
    tr.className = 'border-b';
    tr.innerHTML = `
      <td class="px-4 py-3">
        <input type="text" name="items[${rowId}][produit]" value="${name}"
               class="w-full border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
               placeholder="Nom du produit" required>
      </td>
      <td class="px-4 py-3">
        <input type="text" name="items[${rowId}][description]" value="${desc}"
               class="w-full border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
               placeholder="Description">
      </td>
      <td class="px-4 py-3">
        <input type="text" inputmode="decimal" name="items[${rowId}][quantite]" value="1"
               class="w-24 border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50 no-spinners"
               aria-label="Quantité (décimal)">
      </td>
      <td class="px-4 py-3">
        <input type="text" inputmode="decimal" name="items[${rowId}][prix_unitaire]" value="${price}"
               class="w-28 border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50 no-spinners"
               aria-label="Prix unitaire">
      </td>
      <td class="px-4 py-3">
        <input type="text" inputmode="decimal" name="items[${rowId}][taux_tva]" value="${tva}"
               class="w-20 border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50 no-spinners"
               aria-label="Taux TVA">
      </td>
      <td class="px-4 py-3">
        <input type="text" inputmode="decimal" name="items[${rowId}][remise]" value="0"
               class="w-20 border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50 no-spinners"
               aria-label="Remise">
      </td>
      <td class="px-4 py-3 text-right font-medium"><span class="total-cell">0.00 €</span></td>
      <td class="px-4 py-3">
        <button type="button" class="delete-row text-red-500 hover:text-red-700" title="Supprimer">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
        </button>
      </td>
    `;
    document.getElementById('itemsTable').appendChild(tr);

    // events
    tr.querySelectorAll('input').forEach(i=>{
      i.addEventListener('input', calculateTotals);
    });
    tr.querySelector('.delete-row').addEventListener('click', ()=>{ tr.remove(); calculateTotals(); });

    calculateTotals();
  }

  // Initial empty row
  addRow();

  // Add product buttons
  document.getElementById('addProductBtn')?.addEventListener('click', ()=>addRow());

  // Catalog modal handlers
  const modal = document.getElementById('productCatalogModal');
  document.getElementById('addFromCatalogBtn')?.addEventListener('click', ()=>modal.classList.remove('hidden'));
  document.getElementById('closeCatalogBtn')?.addEventListener('click', ()=>modal.classList.add('hidden'));
  document.getElementById('selectAllProduits')?.addEventListener('change', (e)=>{
    document.querySelectorAll('.produit-checkbox').forEach(cb=>cb.checked=e.target.checked);
  });
  document.getElementById('addSelectedBtn')?.addEventListener('click', ()=>{
    const sel = document.querySelectorAll('.produit-checkbox:checked');
    if (!sel.length) { alert('Veuillez sélectionner au moins un produit'); return; }
    sel.forEach(cb=>{
      addRow({ name: cb.dataset.name, description: cb.dataset.description, price: cb.dataset.price, tva: cb.dataset.tva });
    });
    modal.classList.add('hidden');
    document.getElementById('selectAllProduits').checked = false;
    document.querySelectorAll('.produit-checkbox').forEach(cb=>cb.checked=false);
  });

  // Catalog search
  document.getElementById('catalogSearch')?.addEventListener('input', function(e){
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#catalogTable tr').forEach(tr=>{
      const name = tr.children[1].textContent.toLowerCase();
      const desc = tr.children[2].textContent.toLowerCase();
      const code = tr.children[3].textContent.toLowerCase();
      tr.style.display = (name.includes(q) || desc.includes(q) || code.includes(q)) ? '' : 'none';
    });
  });

  // Totals
  function calculateTotals(){
    let totalHT = 0, totalTVA = 0;

    document.querySelectorAll('#itemsTable tr').forEach(tr=>{
      const q   = toNumber(tr.querySelector('input[name*="[quantite]"]').value);
      const pu  = toNumber(tr.querySelector('input[name*="[prix_unitaire]"]').value);
      const tva = toNumber(tr.querySelector('input[name*="[taux_tva]"]').value);
      const rem = toNumber(tr.querySelector('input[name*="[remise]"]').value);

      let rowHT = q * pu;
      if (rem > 0) rowHT -= rowHT * (rem/100);
      const rowTVA = rowHT * (tva/100);

      tr.querySelector('.total-cell').textContent = rowHT.toFixed(2)+' €';

      totalHT += rowHT;
      totalTVA += rowTVA;
    });

    document.getElementById('total-ht').textContent = totalHT.toFixed(2)+' €';
    document.getElementById('tva').textContent     = totalTVA.toFixed(2)+' €';
    document.getElementById('total-ttc').textContent= (totalHT+totalTVA).toFixed(2)+' €';
  }

  // Submit validation
  document.getElementById('devisForm').addEventListener('submit', function(e){
    const rows = document.querySelectorAll('#itemsTable tr');
    if (!rows.length) { e.preventDefault(); alert('Veuillez ajouter au moins un produit'); return; }

    let ok = true;
    rows.forEach(tr=>{
      const name = tr.querySelector('input[name*="[produit]"]').value.trim();
      const q    = tr.querySelector('input[name*="[quantite]"]').value.trim();
      const pu   = tr.querySelector('input[name*="[prix_unitaire]"]').value.trim();
      const tva  = tr.querySelector('input[name*="[taux_tva]"]').value.trim();
      if (!name || !q || !pu || !tva) { ok=false; tr.style.backgroundColor='#fff0f0'; }
    });
    if (!ok) { e.preventDefault(); alert('Veuillez remplir tous les champs requis pour chaque produit'); }
  });

  // Initial totals
  calculateTotals();
});
</script>
@endsection