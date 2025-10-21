@extends('layout')

@section('content')
<div class="max-w-lg mx-auto bg-white p-6 rounded shadow mt-10">
    <h2 class="text-orange-600 font-semibold text-sm border-b mb-4 pb-2">
        Achat d'unités (virement bancaire)
    </h2>

    {{-- flashes --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-2 rounded mb-4">
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul class="mt-1 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    @php
        $companyUnits = auth()->user()?->company?->units ?? 0;
    @endphp

    <div class="mb-4 flex items-center justify-between">
        <p class="text-gray-700 font-medium">
            Solde de l’entreprise :
            <span class="text-blue-600 font-bold">{{ $companyUnits }}</span> unité(s).
        </p>
        <span class="text-xs rounded-full px-2.5 py-1 border
            {{ $pack->is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-50 text-gray-600 border-gray-200' }}">
            {{ $pack->is_active ? 'Pack actif' : 'Pack inactif' }}
        </span>
    </div>

    <div class="mb-4 rounded-xl border border-gray-200 p-4 bg-gray-50">
        <div class="text-sm text-gray-600">Prix unitaire (HT)</div>
        <div class="text-xl font-semibold text-gray-900">
            {{ number_format($pack->price_ht, 2, ',', ' ') }} €
        </div>
        <div class="text-xs text-gray-500">TVA appliquée : {{ $tvaRate }}%</div>
    </div>

    <form action="{{ route('units.purchase') }}"
          method="POST"
          id="unitForm"
          enctype="multipart/form-data"
          data-unit-price="{{ (float)$pack->price_ht }}"
          data-tva-rate="{{ (int)$tvaRate }}">
        @csrf

        {{-- Quantité --}}
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
        <input type="number" name="quantity" id="quantity" min="1"
               class="w-full border px-3 py-2 rounded mb-4"
               value="{{ old('quantity', 1) }}" required>

        {{-- Coordonnées bancaires --}}
        <div class="mb-4 rounded border p-3 bg-orange-50">
            <div class="text-sm text-gray-700 mb-2 font-medium">Coordonnées bancaires</div>
            <ul class="text-sm text-gray-800 space-y-1">
                <li><strong>Titulaire :</strong> GS AUTO SARL</li>
                <li><strong>IBAN :</strong> FR76 1234 5678 9101 1121 3141 516</li>
                <li><strong>BIC :</strong> AGRIFRPPXXX</li>
                <li><strong>Référence à indiquer :</strong> <span class="font-mono">UNITS-{{ auth()->id() }}</span></li>
            </ul>
        </div>

        {{-- Preuve --}}
        <label class="block text-sm font-medium text-gray-700 mt-3">Téléversez le reçu du virement (png/jpg/pdf, max 8 Mo)</label>
        <input type="file" name="virement_proof" class="mt-1 mb-4">

        {{-- Totaux dynamiques --}}
        <div class="mb-2">
            <label class="text-sm text-gray-600">TVA ({{ $tvaRate }}%)</label>
            <div id="tvaDisplay" class="text-gray-800">0 €</div>
        </div>
        <div class="mb-4">
            <label class="text-sm text-gray-600">Total TTC</label>
            <div id="totalDisplay" class="text-gray-800 font-bold">0 €</div>
        </div>

        <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600">
            Envoyer la demande
        </button>
    </form>
</div>

<script>
    const form         = document.getElementById('unitForm');
    const quantityInput= document.getElementById('quantity');
    const tvaDisplay   = document.getElementById('tvaDisplay');
    const totalDisplay = document.getElementById('totalDisplay');

    const UNIT_PRICE = parseFloat(form.dataset.unitPrice);       // ✅ from Super Admin
    const TVA_RATE   = parseFloat(form.dataset.tvaRate) / 100.0; // 0.20

    function fmt(n){ return new Intl.NumberFormat('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(n) + ' €'; }

    function updateTotals(){
        const qty = Math.max(0, parseFloat(quantityInput.value || 0));
        const ht  = qty * UNIT_PRICE;
        const tva = ht * TVA_RATE;
        const ttc = ht + tva;
        tvaDisplay.textContent   = fmt(tva);
        totalDisplay.textContent = fmt(ttc);
    }

    quantityInput.addEventListener('input', updateTotals);
    updateTotals();
</script>
@endsection