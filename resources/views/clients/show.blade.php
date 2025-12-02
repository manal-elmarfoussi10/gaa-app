@extends('layout')

@section('content')
<div class="container mx-auto px-4 py-8">
  {{-- Header --}}
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
    <div>
      <div class="flex items-center">
        <h1 class="text-3xl font-bold text-gray-800">
          {{ $client->prenom }} {{ $client->nom_assure }}
        </h1>
        <a href="{{ route('clients.edit', $client->id) }}" class="ml-3 text-cyan-600 hover:text-cyan-800">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
          </svg>
        </a>
      </div>
      <div class="mt-2">
        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
          {{ $client->plaque }}
        </span>
      </div>
    </div>

    <div class="mt-4 md:mt-0">
      <a href="{{ route('clients.export.pdf', $client) }}"
         class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
        Télécharger le dossier
      </a>
    </div>
  </div>

{{-- Signature (GS Auto) --}}
@php
  $isSigned    = ($client->statut_gsauto === 'signed') || ((int)$client->statut_signature === 1);
  $hasUnsigned = !empty($client->contract_pdf_path);
  // Model accessor supports both columns (contract_signed_pdf_path / signed_pdf_path)
  $signedPath  = $client->contract_signed_pdf_path;
  $hasSigned   = !empty($signedPath);
@endphp

<div id="signature-block" class="bg-white rounded-xl shadow-md p-6 mb-8">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 md:gap-6">
    <div class="flex-1 min-w-0">
      <h2 class="text-xl font-semibold text-gray-800 flex items-center">
        Signature électronique (GS Auto)
        <span class="ml-3 text-xs font-medium px-3 py-1 rounded-full {{ $isSigned ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
          {{ $isSigned ? 'SIGNÉ' : 'EN ATTENTE' }}
        </span>
      </h2>

      @if($client->signed_at)
        <p class="text-sm text-gray-500 mt-1">
          Signé le : {{ \Carbon\Carbon::parse($client->signed_at)->format('d/m/Y H:i') }}
        </p>
      @endif

      <p class="text-sm text-gray-600 mt-1 truncate">
        Générez d’abord le contrat PDF, puis envoyez-le au client pour signature.
      </p>

      @if($client->yousign_signature_request_id)
        <p class="text-xs text-gray-400 mt-1">
          SR : {{ $client->yousign_signature_request_id }}
          @if($client->yousign_document_id)
            · Doc : {{ $client->yousign_document_id }}
          @endif
        </p>
      @endif
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 flex-wrap">
      @if(!$isSigned)
        {{-- Generate / Regenerate --}}
        <form method="POST" action="{{ route('clients.contract.generate', $client) }}" class="inline-block m-0">
          @csrf
          <button type="submit" class="inline-flex items-center bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            {{ $hasUnsigned ? 'Régénérer le contrat' : 'Générer le contrat' }}
          </button>
        </form>

        {{-- Download (unsigned) --}}
        @if($hasUnsigned)
          <a href="{{ route('clients.contract.download', $client) }}"
             class="inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium">
            Télécharger le contrat
          </a>
        @endif

        {{-- Send / Resend --}}
        @php $canSend = $hasUnsigned; @endphp
        @if(!$client->statut_gsauto || $client->statut_gsauto === 'draft')
          <form method="POST" action="{{ route('clients.send_signature', $client->id) }}" class="inline-block m-0">
            @csrf
            <button type="submit"
              class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ $canSend ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'bg-orange-200 text-white/70 cursor-not-allowed' }}"
              {{ $canSend ? '' : 'disabled' }}>
              Envoyer pour signature
            </button>
          </form>
        @elseif($client->statut_gsauto === 'failed')
          <form method="POST" action="{{ route('clients.resend_signature', $client->id) }}" class="inline-block m-0">
            @csrf
            <button type="submit"
              class="inline-flex items-center bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
              Renvoyer (échec)
            </button>
          </form>
        @endif

      @else
        {{-- Signed: only the signed download (or a waiting badge) --}}
        @if($hasSigned)
          <a href="{{ route('clients.contract.download_signed', $client->id) }}"
             class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Télécharger le contrat signé
          </a>
        @else
          <span class="inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-lg text-sm">
            Déjà signé (en cours d’archivage…)
          </span>
        @endif
      @endif
    </div>
  </div>

  {{-- Alerts --}}
  @if(session('success'))
    <div class="mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-2 rounded">{{ session('error') }}</div>
  @endif
</div>

@if(session('open_signature'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const el = document.getElementById('signature-block');
      if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  </script>
@endif

  {{-- Statut du dossier --}}
  <div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
      <div class="mb-4 md:mb-0">
        <h2 class="text-lg font-semibold text-gray-800">Statut du dossier</h2>
        <div class="flex items-center mt-2">
          <span class="bg-orange-100 text-orange-800 text-sm font-medium px-3 py-1 rounded-full">
            {{ $client->statut ?? 'En attente' }}
          </span>
          <span class="ml-3 text-sm text-gray-600">
            Créé le: {{ $client->created_at->format('d/m/Y') }}
          </span>
        </div>
      </div>

      <form method="POST" action="{{ route('clients.statut_interne', $client->id) }}" class="w-full md:w-auto">
        @csrf
        <div class="flex flex-col md:flex-row items-start md:items-center">
          <div class="mr-4 mb-2 md:mb-0">
            <label for="statut_interne" class="block text-sm font-medium text-gray-700">Statut interne:</label>
            <select name="statut_interne" id="statut_interne"
                    class="mt-1 block w-full md:w-64 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
              <option value="">-- Aucun --</option>
              @foreach([
                'En attente document','Faire devis','Fixer RDV','Faire commande',
                'En attente de pose','Pose terminée','Annulée'
              ] as $opt)
                <option value="{{ $opt }}" {{ $client->statut_interne === $opt ? 'selected' : '' }}>
                  {{ $opt }}
                </option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Mettre à jour
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Quick actions --}}
  <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('clients.edit', $client->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg flex flex-col items-center justify-center transition-all hover:shadow-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
      </svg>
      <span class="text-sm">Modifier dossier</span>
    </a>

    <a href="{{ route('factures.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg flex flex-col items-center justify-center transition-all hover:shadow-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span class="text-sm">Acquitter facture</span>
    </a>

    <a href="{{ route('sidexa.index', ['client_id' => $client->id]) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg flex flex-col items-center justify-center transition-all hover:shadow-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
      <span class="text-sm">Faire chiffrage</span>
    </a>
  </div>

  {{-- Main grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Client info --}}
    <div class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Informations Client</h2>
        <div class="w-8 h-8 rounded-full bg-cyan-100 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
      </div>
      <div class="space-y-3">
        <div><p class="text-sm text-gray-500">Nom de l'assuré</p><p class="font-medium">{{ $client->nom_assure }}</p></div>
        <div><p class="text-sm text-gray-500">Prénom</p><p class="font-medium">{{ $client->prenom }}</p></div>
        <div><p class="text-sm text-gray-500">Adresse</p><p class="font-medium">{{ $client->adresse }}</p></div>
        <div><p class="text-sm text-gray-500">Email</p>
          <p class="font-medium text-cyan-600"><a href="mailto:{{ $client->email }}">{{ $client->email }}</a></p></div>
        <div><p class="text-sm text-gray-500">Téléphone</p>
          <p class="font-medium text-cyan-600"><a href="tel:{{ $client->telephone }}">{{ $client->telephone }}</a></p></div>
      </div>
    </div>

    {{-- Vehicle --}}
    <div class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Véhicule</h2>
        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
          </svg>
        </div>
      </div>
      <div class="space-y-3">
        <div><p class="text-sm text-gray-500">Immatriculation</p><p class="font-medium">{{ $client->plaque }}</p></div>
        <div><p class="text-sm text-gray-500">Marque</p><p class="font-medium">{{ $client->marque ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Modèle</p><p class="font-medium">{{ $client->modele ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Kilométrage</p><p class="font-medium">{{ $client->kilometrage ?? '-' }} km</p></div>
        <div><p class="text-sm text-gray-500">Type de vitrage</p><p class="font-medium">{{ $client->type_vitrage ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Ancien modèle plaque</p><p class="font-medium">{{ $client->ancien_modele_plaque ?? '-' }}</p></div>
      </div>
    </div>

    {{-- Insurance --}}
    <div class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Assurance</h2>
        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
      </div>
      <div class="space-y-3">
        <div><p class="text-sm text-gray-500">Nom</p><p class="font-medium">{{ $client->nom_assurance }}</p></div>
        <div><p class="text-sm text-gray-500">N° Police</p><p class="font-medium">{{ $client->numero_police }}</p></div>
        <div><p class="text-sm text-gray-500">N° Sinistre</p><p class="font-medium">{{ $client->numero_sinistre ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Autre assurance</p><p class="font-medium">{{ $client->autre_assurance ?? '-' }}</p></div>
      </div>
    </div>
  </div>

  {{-- Extra info --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Détails du Sinistre</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><p class="text-sm text-gray-500">Date du sinistre</p>
          <p class="font-medium">{{ $client->date_sinistre ? \Carbon\Carbon::parse($client->date_sinistre)->format('d/m/Y') : '-' }}</p>
        </div>
        <div><p class="text-sm text-gray-500">Date d'enregistrement</p>
          <p class="font-medium">{{ $client->date_declaration ? \Carbon\Carbon::parse($client->date_declaration)->format('d/m/Y') : '-' }}</p>
        </div>
        <div><p class="text-sm text-gray-500">Raison</p><p class="font-medium">{{ $client->raison ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Réparation</p><p class="font-medium">{{ $client->reparation ? 'Oui' : 'Non' }}</p></div>
        <div><p class="text-sm text-gray-500">Connu par</p><p class="font-medium">{{ $client->connu_par ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Adresse de pose</p><p class="font-medium">{{ $client->adresse_pose ?? '-' }}</p></div>
        <div class="md:col-span-2"><p class="text-sm text-gray-500">Précisions</p><p class="font-medium">{{ $client->precision ?? '-' }}</p></div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Informations Financières</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><p class="text-sm text-gray-500">Total Factures (HT)</p>
          <p class="font-medium text-blue-600">{{ number_format($client->factures->sum('total_ht'), 2, ',', ' ') }} €</p>
        </div>
        <div><p class="text-sm text-gray-500">Total Avoirs (HT)</p>
          <p class="font-medium text-green-600">
            @php $totalAvoirs = 0; foreach ($client->factures as $facture) { $totalAvoirs += $facture->avoirs->sum('montant_ht'); } @endphp
            {{ number_format($totalAvoirs, 2, ',', ' ') }} €
          </p>
        </div>
        <div><p class="text-sm text-gray-500">Total Devis (HT)</p>
          <p class="font-medium text-purple-600">{{ number_format($client->devis->sum('total_ht'), 2, ',', ' ') }} €</p>
        </div>
        <div><p class="text-sm text-gray-500">Encaissé</p><p class="font-medium text-cyan-600">{{ $client->encaisse ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Cadeau</p><p class="font-medium">{{ $client->type_cadeau ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Référence interne</p><p class="font-medium">{{ $client->reference_interne ?? '-' }}</p></div>
        <div><p class="text-sm text-gray-500">Référence client</p><p class="font-medium">{{ $client->reference_client ?? '-' }}</p></div>
      </div>
    </div>
  </div>


  {{-- === Pièces commerciales : Devis / Factures / Avoirs === --}}
@php
// Make sure the controller loaded: $client->load('devis','factures.avoirs')
$avoirs = $client->factures?->flatMap->avoirs ?? collect();
@endphp

<div class="bg-white rounded-xl shadow-md p-6 mb-8">
<div class="flex items-center justify-between mb-4">
  <h2 class="text-lg font-semibold text-gray-800">Pièces commerciales</h2>
</div>

{{-- Tabs header --}}
<div class="flex gap-2 mb-4">
  <button type="button" data-tab="tab-devis"
    class="tab-btn px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 text-sm font-medium">Devis ({{ $client->devis->count() }})</button>
  <button type="button" data-tab="tab-factures"
    class="tab-btn px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 text-sm font-medium">Factures ({{ $client->factures->count() }})</button>
  <button type="button" data-tab="tab-avoirs"
    class="tab-btn px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 text-sm font-medium">Avoirs ({{ $avoirs->count() }})</button>
</div>

{{-- Devis --}}
<div id="tab-devis" class="tab-panel">
  <div class="overflow-x-auto rounded-lg border">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
          <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total HT</th>
          <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total TTC</th>
          <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white">
        @forelse($client->devis as $d)
          <tr>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
              {{ optional($d->created_at)->format('d/m/Y') }}
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-cyan-700">
              {{ $d->numero ?? ('Devis #'.$d->id) }}
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
              {{ number_format($d->total_ht ?? 0, 2, ',', ' ') }} €
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
              {{ number_format($d->total_ttc ?? 0, 2, ',', ' ') }} €
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
              <a href="#"
                 class="js-open-preview text-blue-600 hover:underline"
                 data-url="{{ route('devis.preview', $d->id) }}">
                Voir
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Aucun devis.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Factures --}}
<div id="tab-factures" class="tab-panel hidden">
  <div class="overflow-x-auto rounded-lg border">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
          <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">HT</th>
          <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">TTC</th>
          <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white">
        @forelse($client->factures as $f)
          <tr>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
              {{ optional($f->created_at)->format('d/m/Y') }}
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-cyan-700">
              {{ $f->numero ?? ('Facture #'.$f->id) }}
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
              {{ number_format($f->total_ht ?? 0, 2, ',', ' ') }} €
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
              {{ number_format($f->total_ttc ?? 0, 2, ',', ' ') }} €
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
              <a href="#"
                 class="js-open-preview text-blue-600 hover:underline"
                 data-url="{{ route('factures.preview', $f->id) }}">
                Voir
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Aucune facture.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Avoirs --}}
<div id="tab-avoirs" class="tab-panel hidden">
  <div class="overflow-x-auto rounded-lg border">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Facture associée</th>
          <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
          <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white">
        @forelse($avoirs as $a)
          <tr>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
              {{ optional($a->created_at)->format('d/m/Y') }}
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm">
              {{ optional($a->facture)->numero ? 'Facture '.$a->facture->numero : ('#'.$a->facture_id) }}
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
              {{ number_format($a->montant ?? 0, 2, ',', ' ') }} €
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
              <a href="#"
                 class="js-open-preview text-blue-600 hover:underline"
                 data-url="{{ route('avoirs.preview', $a->id) }}">
                Voir
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Aucun avoir.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>

{{-- Modal preview (iframe) --}}
<div id="docPreviewModal" class="fixed inset-0 z-50 hidden">
<div class="absolute inset-0 bg-black/60"></div>
<div class="relative mx-auto mt-8 w-11/12 max-w-5xl bg-white rounded-xl shadow-2xl">
  <div class="flex items-center justify-between px-4 py-3 border-b">
    <h3 class="font-semibold text-gray-800">Aperçu du document</h3>
    <button type="button" class="js-close-preview p-2 rounded hover:bg-gray-100">&times;</button>
  </div>
  <div class="p-4">
    <iframe id="docPreviewFrame" src="about:blank" class="w-full h-[75vh] border rounded-lg"></iframe>
  </div>
</div>
</div>

{{-- Documents --}}
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-gray-800">Documents</h2>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @php
      $documents = [
        'photo_vitrage'     => 'Photo Vitrage',
        'photo_carte_verte' => 'Carte Verte',
        'photo_carte_grise' => 'Carte Grise',
      ];
      $hasDocuments = false;
    @endphp

    @foreach($documents as $field => $label)
      @if(!empty($client->$field))
        @php
          $hasDocuments = true;
          $path = $client->$field;
          $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
          $docUrl = route('attachment', ['path' => $path]);
        @endphp

        <div class="border rounded-lg overflow-hidden">
          <div class="bg-gray-100 h-48 flex items-center justify-center">
            @if($ext === 'pdf')
              <div class="text-center p-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-sm font-medium text-gray-700 truncate">{{ $label }}</p>
              </div>
            @else
              <img src="{{ $docUrl }}" alt="{{ $label }}" class="object-contain w-full h-full">
            @endif
          </div>
          <div class="p-3">
            <h3 class="font-medium text-gray-800">{{ $label }}</h3>
            <div class="flex justify-between mt-2">
              {{-- Voir (inline open) --}}
              <a href="{{ $docUrl }}" target="_blank"
                 class="text-cyan-600 hover:text-cyan-800 text-sm flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Voir
              </a>
              {{-- Télécharger (force download) --}}
              <a href="{{ $docUrl }}?download=1" 
                 class="text-gray-600 hover:text-gray-800 text-sm flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Télécharger
              </a>
            </div>
          </div>
        </div>
      @endif
    @endforeach

    @if(!$hasDocuments)
      <div class="col-span-3 text-center py-8">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="mt-2 text-gray-500">Aucun document disponible</p>
      </div>
    @endif
  </div>
</div>

  {{-- Conversations --}}
  <div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Conversations</h2>
      <button id="newConversationBtn" type="button"
              class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-md text-sm font-medium">
        Nouvelle conversation
      </button>
    </div>

    <div id="newConversationForm" class="hidden mb-8">
      <form method="POST" action="{{ route('clients.conversations.store', $client) }}"
            enctype="multipart/form-data" class="grid grid-cols-1 gap-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-gray-700">Sujet</label>
          <input type="text" name="subject" required
                 class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-cyan-500 focus:border-cyan-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Message</label>
          <textarea name="content" rows="3" required
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-cyan-500 focus:border-cyan-500"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Fichier joint</label>
          <input type="file" name="file"
                 class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">
        </div>

        <div class="flex justify-end">
          <button type="button" id="cancelNewConversation"
                  class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
            Annuler
          </button>
          <button type="submit"
                  class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Envoyer
          </button>
        </div>
      </form>
    </div>

    <div id="messages-list" class="space-y-6">
      @include('clients.partials._messages', [
        'emails' => ($emails ?? $client->emails()
          ->with(['senderUser','receiverUser','replies.sender','replies.receiver'])
          ->orderBy('created_at')
          ->get())
      ])
    </div>

    <audio id="notificationSound" src="{{ asset('audio/notification.mp3') }}" preload="auto"></audio>
  </div>

  {{-- Threads (optional) --}}
  <div id="threads-list" class="space-y-6">
    @forelse($client->conversations as $thread)
      <div class="thread border rounded-lg p-4">
        <div class="flex justify-between">
          <h3 class="font-semibold">[{{ $client->nom_assure }}] - {{ $thread->subject }}</h3>
          <span class="text-sm text-gray-500">
            Démarrée par {{ $thread->creator->name ?? 'Utilisateur supprimé' }}
            le {{ $thread->created_at->format('d/m/Y H:i') }}
          </span>
        </div>

        <div class="mt-4">
          @foreach($thread->emails as $email)
            <div class="email mb-6">
              <div class="flex items-start">
                <div class="w-8 h-8 rounded-full bg-cyan-100 flex items-center justify-center">
                  <span class="text-cyan-800 text-sm font-medium">
                    {{ strtoupper(substr($email->senderUser->name ?? '??', 0, 2)) }}
                  </span>
                </div>
                <div class="ml-3">
                  <p class="font-medium">{{ $email->senderUser->name ?? 'Utilisateur inconnu' }}</p>
                  <p class="text-sm text-gray-500">
                    à {{ $email->receiverUser->name ?? 'GS AUTO' }} · {{ $email->created_at->format('d/m/Y H:i') }}
                  </p>
                  <div class="mt-2 text-gray-700">{!! nl2br(e($email->content)) !!}</div>

                  @if($email->file_path)
                    <div class="mt-2">
                      <a href="{{ asset('storage/'.$email->file_path) }}" target="_blank"
                         class="text-cyan-600 hover:text-cyan-800">📎 {{ $email->file_name }}</a>
                    </div>
                  @endif
                </div>
              </div>

              <div class="replies pl-8 mt-4 space-y-4">
                @foreach($email->replies as $reply)
                  <div class="reply">
                    <div class="flex items-start">
                      <div class="w-8 h-8 rounded-full bg-cyan-100 flex items-center justify-center">
                        <span class="text-cyan-800 text-sm font-medium">
                          {{ strtoupper(substr($reply->sender->name ?? '??', 0, 2)) }}
                        </span>
                      </div>
                      <div class="ml-3">
                        <p class="font-medium">{{ $reply->sender->name ?? 'Utilisateur inconnu' }}</p>
                        <p class="text-sm text-gray-500">
                          à {{ $reply->receiver->name ?? 'GS AUTO' }} · {{ $reply->created_at->format('d/m/Y H:i') }}
                        </p>
                        <div class="mt-2 text-gray-700">{!! nl2br(e($reply->content)) !!}</div>

                        @if($reply->file_path)
                          <div class="mt-2">
                            <a href="{{ asset('storage/'.$reply->file_path) }}" target="_blank"
                               class="text-cyan-600 hover:text-cyan-800">📎 {{ $reply->file_name }}</a>
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>

        @php $targetEmail = $thread->emails->last(); @endphp
        @if($targetEmail)
          <form method="POST" action="{{ route('conversations.reply', $targetEmail->id) }}"
                enctype="multipart/form-data" class="mt-4">
            @csrf
            <label class="block text-sm font-medium text-gray-700">Répondre</label>
            <textarea name="content" rows="3" required
                      class="mt-1 mb-3 w-full py-2 px-3 border rounded-md focus:ring-cyan-500 focus:border-cyan-500"></textarea>

            <label class="block text-sm font-medium text-gray-700">Fichier joint</label>
            <input type="file" name="file"
                   class="mt-1 mb-4 w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                          file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">

            <div class="flex justify-end">
              <button type="submit"
                      class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Envoyer
              </button>
            </div>
          </form>
        @endif
      </div>
    @empty
      <p class="text-gray-500">Aucune conversation pour ce client.</p>
    @endforelse
  </div>

  {{-- Timeline --}}
  <div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Historique du dossier</h2>
    <div class="relative pl-8 border-l-2 border-gray-200 space-y-6">
      <div class="relative">
        <div class="absolute -left-11 top-0 w-6 h-6 rounded-full bg-cyan-500 flex items-center justify-center">
          <div class="w-2 h-2 rounded-full bg-white"></div>
        </div>
        <div class="pl-4">
          <p class="font-medium text-gray-800">Dossier créé</p>
          <p class="text-sm text-gray-500">{{ $client->created_at->format('d/m/Y H:i') }}</p>
        </div>
      </div>

      <div class="relative">
        <div class="absolute -left-11 top-0 w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
          <div class="w-2 h-2 rounded-full bg-white"></div>
        </div>
        <div class="pl-4">
          <p class="font-medium text-gray-800">Dossier envoyé à l'assurance</p>
          <p class="text-sm text-gray-500">19/07/2025 10:30</p>
        </div>
      </div>

      <div class="relative">
        <div class="absolute -left-11 top-0 w-6 h-6 rounded-full bg-yellow-500 flex items-center justify-center">
          <div class="w-2 h-2 rounded-full bg-white"></div>
        </div>
        <div class="pl-4">
          <p class="font-medium text-gray-800">En attente de validation</p>
          <p class="text-sm text-gray-500">En cours...</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')

<script>
  // Tabs
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-tab');
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
      document.getElementById(id)?.classList.remove('hidden');
      // style active
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('bg-cyan-600','text-white'));
      btn.classList.add('bg-cyan-600','text-white');
    });
  });
  // Set first tab active by default
  document.querySelector('.tab-btn')?.click();

  // Preview modal
  const modal  = document.getElementById('docPreviewModal');
  const frame  = document.getElementById('docPreviewFrame');
  const closeB = modal?.querySelector('.js-close-preview');

  document.addEventListener('click', (e) => {
    const a = e.target.closest('.js-open-preview');
    if (!a) return;
    e.preventDefault();
    const url = a.getAttribute('data-url');
    if (!url) return;
    frame.src = url;
    modal.classList.remove('hidden');
  });

  closeB?.addEventListener('click', () => {
    modal.classList.add('hidden');
    frame.src = 'about:blank';
  });
  modal?.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.add('hidden');
      frame.src = 'about:blank';
    }
  });
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
  const btnOpen   = document.getElementById('newConversationBtn');
  const formWrap  = document.getElementById('newConversationForm');
  const btnCancel = document.getElementById('cancelNewConversation');
  const listWrap  = document.getElementById('messages-list');
  const sound     = document.getElementById('notificationSound');

  if (btnOpen && formWrap)  btnOpen.addEventListener('click', () => formWrap.classList.remove('hidden'));
  if (btnCancel && formWrap) btnCancel.addEventListener('click', () => formWrap.classList.add('hidden'));

  // Live refresh every 5s
  let lastCount = document.querySelectorAll('#messages-list .p-4.border').length;
  setInterval(() => {
    fetch("{{ route('conversations.fetch', $client->id) }}", { headers: {'X-Requested-With':'XMLHttpRequest'} })
      .then(r => r.text())
      .then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newCount = temp.querySelectorAll('.p-4.border').length;
        if (newCount > lastCount) { try { sound && sound.play(); } catch(e) {} }
        lastCount = newCount;
        listWrap.innerHTML = html;
      })
      .catch(() => {});
  }, 5000);
});
</script>
@endsection

<style>
  .container { max-width: 1200px; }
  .bg-cyan-100 { background-color: #ecfeff; }
  .bg-cyan-600 { background-color: #0891b2; }
  .hover\:bg-cyan-700:hover { background-color: #0e7490; }
  .bg-blue-100 { background-color: #dbeafe; }
  .bg-green-100 { background-color: #dcfce7; }
  .bg-orange-100 { background-color: #ffedd5; }
</style>