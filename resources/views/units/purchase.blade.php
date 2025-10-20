@extends('layout')

@section('content')
<div class="max-w-lg mx-auto bg-white p-6 rounded shadow mt-10">
    <h2 class="text-orange-600 font-semibold text-sm border-b mb-4 pb-2">
        Achat d'unités (virement bancaire)
    </h2>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul class="mt-1 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $companyUnits = auth()->user()?->company?->units ?? 0;
    @endphp

    <p class="mb-4 text-gray-700 font-medium">
        Solde de l’entreprise : <span class="text-blue-600 font-bold">{{ $companyUnits }}</span> unité(s).
    </p>

    <form action="{{ route('units.purchase') }}" method="POST" id="unitForm" enctype="multipart/form-data">
        @csrf

        {{-- Quantité --}}
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
        <input
            type="number"
            name="quantity"
            id="quantity"
            min="1"
            class="w-full border px-3 py-2 rounded mb-4"
            value="{{ old('quantity', 1) }}"
            required
        >

        {{-- Infos virement --}}
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

        {{-- Totaux --}}
        <div class="mb-2">
            <label class="text-sm text-gray-600">TVA (20%)</label>
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
    const quantityInput = document.getElementById('quantity');
    const tvaDisplay = document.getElementById('tvaDisplay');
    const totalDisplay = document.getElementById('totalDisplay');

    function fmt(n){ return new Intl.NumberFormat('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}).format(n) + ' €'; }

    const UNIT_PRICE = 10; // € HT
    const TVA_RATE   = 0.20;

    function updateTotals(){
        const qty = Math.max(0, parseFloat(quantityInput.value || 0));
        const subtotal = qty * UNIT_PRICE;
        const tva = subtotal * TVA_RATE;
        const total = subtotal + tva;
        tvaDisplay.textContent   = fmt(tva);
        totalDisplay.textContent = fmt(total);
    }

    quantityInput.addEventListener('input', updateTotals);
    updateTotals();
</script>
@endsection