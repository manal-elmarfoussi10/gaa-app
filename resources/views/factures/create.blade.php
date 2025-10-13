@extends('layout')

@section('content')
<div class="p-4 sm:p-6">

    <style>
        /* Hide number input spinners */
        input[type=number].no-spinners::-webkit-outer-spin-button,
        input[type=number].no-spinners::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number].no-spinners { -moz-appearance: textfield; appearance: textfield; }
    </style>

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 p-6 bg-white rounded-xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Créer une nouvelle facture</h1>
            <p class="text-gray-600 mt-1">Ajoutez des produits et services à votre facture</p>
        </div>
        <a href="{{ route('factures.index') }}" class="flex items-center gap-2 text-orange-600 hover:text-orange-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Retour à la liste
        </a>
    </div>

    <form action="{{ route('factures.store') }}" method="POST" id="factureForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @csrf

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

            <!-- Mode destinataire -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <label class="border rounded-lg p-3 flex items-start gap-3 cursor-pointer has-[:checked]:border-orange-400">
                    <input type="radio" name="client_mode" value="existing" class="mt-1"
                           {{ old('client_mode','existing')==='existing'?'checked':'' }}>
                    <div>
                        <div class="font-medium">Client existant</div>
                        <div class="text-sm text-gray-600">Rechercher dans vos clients</div>
                    </div>
                </label>

                <label class="border rounded-lg p-3 flex items-start gap-3 cursor-pointer has-[:checked]:border-orange-400">
                    <input type="radio" name="client_mode" value="prospect" class="mt-1"
                           {{ old('client_mode')==='prospect'?'checked':'' }}>
                    <div>
                        <div class="font-medium">Prospect</div>
                        <div class="text-sm text-gray-600">Sans créer de fiche client</div>
                    </div>
                </label>
            </div>

            <!-- Client existant -->
            <div id="section-existing" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Client</label>
                    <div class="relative">
                        <select id="client_id" name="client_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50 appearance-none">
                            <option value="">-- Sélectionner un client --</option>
                            @foreach($clients as $client)
                                <option
                                    value="{{ $client->id }}"
                                    data-km="{{ $client->kilometrage ?? '' }}"
                                    data-plaque="{{ $client->plaque ?? '' }}"
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

                    <!-- Badge Kilométrage -->
                    <div id="kmBadge" class="hidden mt-3 inline-flex items-center gap-2 text-sm bg-gray-100 text-gray-800 rounded-full px-3 py-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2a10 10 0 100 20 10 10 0 000-20Zm1 5v6.59l4.3 4.3-1.42 1.42L11 14V7h2Z"/>
                        </svg>
                        <span><strong>Kilométrage :</strong> <span id="kmValue">—</span> km</span>
                    </div>
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Date de facture <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="date" name="date_facture"
                               value="{{ old('date_facture', date('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 pl-10 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" required>
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prospect (pas de fiche client) -->
            <div id="section-prospect" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 hidden">
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Nom du prospect *</label>
                    <input type="text" name="prospect_name" value="{{ old('prospect_name') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                           placeholder="Ex : Entreprise Martin">
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
            </div>
        </div>

        <!-- Produits / Services -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800">Produits / Services</h2>
                <div class="flex gap-2">
                    <button type="button" id="addProductBtn" class="flex items-center gap-1 text-sm bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-1.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Ajouter un produit
                    </button>
                    <button type="button" id="addFromCatalogBtn" class="flex items-center gap-1 text-sm bg-green-50 text-green-700 hover:bg-green-100 px-3 py-1.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                        </svg>
                        Catalogue
                    </button>
                </div>
            </div>

            <!-- Modal catalogue -->
            <div id="productCatalogModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
                <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl max-h-[80vh] overflow-hidden flex flex-col">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Sélectionner des produits</h3>
                        <button type="button" id="closeCatalogBtn" class="text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
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
                                @if(isset($produits) && count($produits) > 0)
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
                                @else
                                    <tr>
                                        <td colspan="6" class="px-4 py-3 text-center text-gray-500">Aucun produit disponible dans le catalogue</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tableau items -->
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

        {{-- Optional: reset to company defaults (uses $defaults passed by controller) --}}
        <button type="button" id="resetPaymentDefaults"
                class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">
            Remettre les valeurs par défaut
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block mb-2 font-medium text-gray-700">Mode de paiement</label>
            <input type="text" name="payment_method"
                   value="{{ old('payment_method', $defaults['payment_method'] ?? '') }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                   placeholder="Ex : Virement bancaire">
        </div>

        <div>
            <label class="block mb-2 font-medium text-gray-700">Date d'échéance</label>
            <input type="date" name="due_date"
                   value="{{ old('due_date', $defaults['due_date'] ?? now()->addDays(30)->toDateString()) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
        </div>

        <div>
            <label class="block mb-2 font-medium text-gray-700">IBAN</label>
            <input type="text" name="payment_iban"
                   value="{{ old('payment_iban', $defaults['payment_iban'] ?? '') }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                   placeholder="FR..">
        </div>

        <div>
            <label class="block mb-2 font-medium text-gray-700">BIC</label>
            <input type="text" name="payment_bic"
                   value="{{ old('payment_bic', $defaults['payment_bic'] ?? '') }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                   placeholder="QNTOFRP1XXX">
        </div>

        <div>
            <label class="block mb-2 font-medium text-gray-700">Taux des pénalités de retard (%)</label>
            <input type="number" step="0.01" inputmode="decimal" name="penalty_rate"
                   value="{{ old('penalty_rate', $defaults['penalty_rate'] ?? '') }}"
                   class="no-spinners w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                   placeholder="Ex : 10">
        </div>
    </div>

    <label class="inline-flex items-center gap-2 mt-3">
        <input type="checkbox" name="save_as_default" value="1" class="rounded">
        <span class="text-sm text-gray-700">
          Enregistrer ces valeurs comme valeurs par défaut de l’entreprise
        </span>
      </label>

    <div class="mt-6">
        <label class="block mb-2 font-medium text-gray-700">Texte affiché sur la facture</label>
        <textarea name="payment_terms_text" rows="6"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                  placeholder="Saisissez vos modalités (IBAN, BIC, échéance, pénalités, indemnité 40€, etc.)">{{ old('payment_terms_text', $defaults['payment_terms_text'] ?? '') }}</textarea>
        <p class="text-xs text-gray-500 mt-2">
            Astuce : laissez ce champ prérempli et adaptez-le au cas par cas. Ce texte sera imprimé en bas de la facture.
        </p>
    </div>
</div>

        <!-- Submit -->
        <div class="flex justify-end gap-4 pt-4 border-t border-gray-100">
            <a href="{{ route('factures.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Annuler</a>
            <button type="submit" id="submitBtn" class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-6 py-3 rounded-lg transition-all shadow-md hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Créer la facture
            </button>
        </div>
    </form>
</div>

<script>
    (function () {
      const ibanEl   = document.querySelector('[name="payment_iban"]');
      const bicEl    = document.querySelector('[name="payment_bic"]');
      const dueEl    = document.querySelector('[name="due_date"]');
      const textEl   = document.querySelector('[name="payment_terms_text"]');
    
      // if user edits the textarea, stop auto updates
      let userEdited = false;
      if (textEl) {
        textEl.addEventListener('input', () => userEdited = true);
      }
    
      // company name comes from your controller defaults (create/edit already pass $defaults)
      const companyName = @json($defaults['company_name'] ?? 'Votre société');
    
      function frDate(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        // Guard: invalid date -> return raw
        return isNaN(d.getTime()) ? iso : d.toLocaleDateString('fr-FR');
      }
    
      function buildFooter() {
        const iban = (ibanEl?.value || '').trim();
        const bic  = (bicEl?.value  || '').trim();
        const due  = frDate(dueEl?.value || '');
    
        let t = `Par virement bancaire ou chèque à l'ordre de ${companyName}\n`;
        if (bic)  t += `Code B.I.C : ${bic}\n`;
        if (iban) t += `Code I.B.A.N : ${iban}\n`;
        if (due)  t += `La présente facture sera payable au plus tard le : ${due}\n`;
        t += `Passé ce délai, sans obligation d’envoi d’une relance, une pénalité sera appliquée conformément au Code de commerce.\n`;
        t += `Une indemnité forfaitaire pour frais de recouvrement de 40€ est également exigible.`;
        return t;
      }
    
      function maybeUpdate() {
        if (!textEl || userEdited) return;
        textEl.value = buildFooter();
      }
    
      ['input','change'].forEach(evt => {
        ibanEl?.addEventListener(evt, maybeUpdate);
        bicEl?.addEventListener(evt, maybeUpdate);
        dueEl?.addEventListener(evt, maybeUpdate);
      });
    
      // If you have a "Remettre les valeurs par défaut" button, also reset the dirty flag:
      document.getElementById('resetPaymentDefaults')?.addEventListener('click', () => {
        userEdited = false;
        maybeUpdate();
      });
    
      // Initial fill (only if user hasn’t typed)
      maybeUpdate();
    })();
    </script>

<script>

    // ---- Reset payment terms to controller defaults ----


document.addEventListener('DOMContentLoaded', function () {


    /* ========== Helpers ========== */
    const $ = (sel, scope=document) => scope.querySelector(sel);
    const $$ = (sel, scope=document) => Array.from(scope.querySelectorAll(sel));

    const sectionExisting = $('#section-existing');
    const sectionProspect = $('#section-prospect');
    const radios = $$('input[name="client_mode"]');
    const clientSelect = $('#client_id');
    const kmBadge = $('#kmBadge');
    const kmValue = $('#kmValue');

    function fmtKm(val) {
        if (val === null || val === undefined || val === '') return null;
        if (!isNaN(val)) {
            return Number(val).toLocaleString('fr-FR', { maximumFractionDigits: 0 });
        }
        return String(val);
    }

    function updateKmBadge() {
        const opt = clientSelect.options[clientSelect.selectedIndex];
        if (!opt || !opt.value) { kmBadge.classList.add('hidden'); return; }
        const km = fmtKm(opt.getAttribute('data-km') || '');
        if (km) {
            kmValue.textContent = km;
            kmBadge.classList.remove('hidden');
        } else {
            kmBadge.classList.add('hidden');
        }
    }

    function toggleDestSections() {
        const mode = radios.find(r => r.checked)?.value || 'existing';
        if (mode === 'existing') {
            sectionExisting.classList.remove('hidden');
            sectionProspect.classList.add('hidden');
            clientSelect.required = true;
            $$('[name="prospect_name"],[name="prospect_email"],[name="prospect_phone"]').forEach(i => i.required = false);
        } else {
            sectionExisting.classList.add('hidden');
            sectionProspect.classList.remove('hidden');
            clientSelect.required = false;
            $('[name="prospect_name"]').required = true;
        }
        updateKmBadge();
    }

    radios.forEach(r => r.addEventListener('change', toggleDestSections));
    clientSelect.addEventListener('change', updateKmBadge);
    toggleDestSections();

    /* ========== Produits / calculs ========== */
    let rowCount = 0;

    // Convertit "1,5" -> "1.5"
    function normalize(value) {
        if (typeof value !== 'string') return value;
        return value.replace(/\s/g, '').replace(',', '.');
    }

    function attachNumberHandlers(scopeEl) {
        scopeEl.querySelectorAll('input[type="number"]').forEach(el => {
            el.addEventListener('input', () => {
                if (el.value.includes(',')) el.value = normalize(el.value);
                calculateTotals();
            });
            el.addEventListener('blur', () => {
                if (el.value.includes(',')) el.value = normalize(el.value);
            });
        });
    }

    function addProduitRow(produit=null) {
        const rowId = rowCount++;
        const tr = document.createElement('tr');
        tr.className = 'border-b';

        const name = produit ? produit.name : '';
        const desc = produit ? produit.description : '';
        const price = produit ? produit.price : '0';
        const tva   = produit ? produit.tva   : '20.00';

        tr.innerHTML = `
            <td class="px-4 py-3">
                <input type="text" name="items[${rowId}][produit]" value="${name}" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" placeholder="Nom du produit" required>
            </td>
            <td class="px-4 py-3">
                <input type="text" name="items[${rowId}][description]" value="${desc}" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" placeholder="Description">
            </td>
            <td class="px-4 py-3">
                <input type="number" step="0.01" inputmode="decimal" name="items[${rowId}][quantite]" value="1" min="0" class="no-spinners w-20 border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" required>
            </td>
            <td class="px-4 py-3">
                <input type="number" step="0.01" inputmode="decimal" name="items[${rowId}][prix_unitaire]" value="${price}" min="0" class="no-spinners w-24 border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" required>
            </td>
            <td class="px-4 py-3">
                <input type="number" step="0.01" inputmode="decimal" name="items[${rowId}][taux_tva]" value="${tva}" min="0" class="no-spinners w-20 border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50" required>
            </td>
            <td class="px-4 py-3">
                <input type="number" step="0.01" inputmode="decimal" name="items[${rowId}][remise]" value="0" min="0" max="100" class="no-spinners w-20 border border-gray-300 rounded px-3 py-2 focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
            </td>
            <td class="px-4 py-3 text-right font-medium"><span class="total-cell">0.00 €</span></td>
            <td class="px-4 py-3">
                <button type="button" class="delete-row-btn text-red-500 hover:text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>
            </td>
        `;
        $('#itemsTable').appendChild(tr);

        tr.querySelectorAll('input').forEach(i => i.addEventListener('input', calculateTotals));
        attachNumberHandlers(tr);

        tr.querySelector('.delete-row-btn').addEventListener('click', () => { tr.remove(); calculateTotals(); });

        calculateTotals();
    }

    // Init
    addProduitRow();

    $('#addProductBtn').addEventListener('click', () => addProduitRow());
    $('#addFromCatalogBtn').addEventListener('click', () => $('#productCatalogModal').classList.remove('hidden'));
    $('#closeCatalogBtn').addEventListener('click', () => $('#productCatalogModal').classList.add('hidden'));
    $('#selectAllProduits').addEventListener('change', e => {
        $$('.produit-checkbox').forEach(cb => cb.checked = e.target.checked);
    });
    $('#addSelectedBtn').addEventListener('click', () => {
        const selected = $$('.produit-checkbox:checked');
        if (!selected.length) return alert('Veuillez sélectionner au moins un produit');
        selected.forEach(cb => addProduitRow({
            name: cb.dataset.name,
            description: cb.dataset.description,
            price: cb.dataset.price,
            tva: cb.dataset.tva
        }));
        $('#productCatalogModal').classList.add('hidden');
        $('#selectAllProduits').checked = false;
        $$('.produit-checkbox').forEach(cb => cb.checked = false);
    });

    function calculateTotals() {
        let totalHT = 0, totalTVA = 0;
        $$('#itemsTable tr').forEach(row => {
            const q = parseFloat(normalize(row.querySelector('input[name*="quantite"]').value)) || 0;
            const p = parseFloat(normalize(row.querySelector('input[name*="prix_unitaire"]').value)) || 0;
            const t = parseFloat(normalize(row.querySelector('input[name*="taux_tva"]').value)) || 0;
            const d = parseFloat(normalize(row.querySelector('input[name*="remise"]').value)) || 0;

            let rowHT = q * p;
            if (d > 0) rowHT -= (rowHT * d / 100);
            const rowTVA = rowHT * (t / 100);

            row.querySelector('.total-cell').textContent = rowHT.toFixed(2) + ' €';
            totalHT += rowHT; totalTVA += rowTVA;
        });
        $('#total-ht').textContent = totalHT.toFixed(2) + ' €';
        $('#tva').textContent = totalTVA.toFixed(2) + ' €';
        $('#total-ttc').textContent = (totalHT + totalTVA).toFixed(2) + ' €';
    }

    // Validate submit
    $('#factureForm').addEventListener('submit', function (e) {
        const rows = $$('#itemsTable tr');
        if (!rows.length) {
            e.preventDefault();
            alert('Veuillez ajouter au moins un produit');
            return;
        }
        let ok = true;
        rows.forEach(row => {
            const a = row.querySelector('input[name*="produit"]').value;
            const b = row.querySelector('input[name*="quantite"]').value;
            const c = row.querySelector('input[name*="prix_unitaire"]').value;
            const d = row.querySelector('input[name*="taux_tva"]').value;
            if (!a || !b || !c || !d) { ok = false; row.style.background = '#fff0f0'; }
        });
        if (!ok) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs requis pour chaque produit');
        }
    });

    // Normalise numbers on load (for some browsers)
    attachNumberHandlers(document);

    // Show km for pre-selected client (old value)
    updateKmBadge();
});
</script>
@endsection