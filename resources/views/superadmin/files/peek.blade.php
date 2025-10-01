{{-- resources/views/superadmin/files/peek.blade.php --}}
@php
  $money = fn($v) => number_format((float)($v ?? 0), 2, ',', ' ').' €';
@endphp

@if($type === 'devis')
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Date</div>
      <div class="font-medium">{{ optional($item->created_at)->format('d/m/Y H:i') }}</div>
    </div>
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Client</div>
      <div class="font-medium">{{ trim(($item->client->nom_assure ?? '').' '.($item->client->prenom ?? '')) }}</div>
    </div>
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Titre</div>
      <div class="font-medium">{{ $item->titre ?? '-' }}</div>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Total HT</div>
        <div class="text-lg font-semibold">{{ $money($item->total_ht) }}</div>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Total TTC</div>
        <div class="text-lg font-semibold">{{ $money($item->total_ttc) }}</div>
      </div>
    </div>
  </div>

@elseif($type === 'factures')
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Date</div>
      <div class="font-medium">{{ $item->date_facture ? \Illuminate\Support\Carbon::parse($item->date_facture)->format('d/m/Y') : '-' }}</div>
    </div>
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Client</div>
      <div class="font-medium">{{ trim(($item->client->nom_assure ?? '').' '.($item->client->prenom ?? '')) }}</div>
    </div>
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Titre</div>
      <div class="font-medium">{{ $item->titre ?? '-' }}</div>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Total HT</div>
        <div class="text-lg font-semibold">{{ $money($item->total_ht) }}</div>
      </div>
      <div class="bg-gray-50 rounded-xl p-3">
        <div class="text-xs text-gray-500">Total TTC</div>
        <div class="text-lg font-semibold">{{ $money($item->total_ttc) }}</div>
      </div>
    </div>

    <div class="mt-3">
      <div class="text-xs text-gray-500 mb-1">Avoirs liés</div>
      @forelse(($item->avoirs ?? []) as $av)
        <div class="flex items-center justify-between text-sm py-1">
          <span>{{ optional($av->created_at)->format('d/m/Y') }}</span>
          <span class="font-medium">{{ $money($av->montant ?? $av->montant_ht ?? 0) }}</span>
        </div>
      @empty
        <div class="text-gray-500 text-sm">Aucun avoir.</div>
      @endforelse
    </div>

    <div class="mt-4">
      <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium {{ $item->is_paid ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
        {{ $item->is_paid ? 'Payée' : 'Non payée' }}
      </span>
    </div>
  </div>

@elseif($type === 'avoirs')
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Date</div>
      <div class="font-medium">{{ optional($item->created_at)->format('d/m/Y H:i') }}</div>
    </div>
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Client</div>
      <div class="font-medium">
        {{ trim(($item->facture->client->nom_assure ?? '').' '.($item->facture->client->prenom ?? '')) }}
      </div>
    </div>
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-500">Facture</div>
      <div class="font-medium">{{ $item->facture->titre ?? '-' }}</div>
    </div>
    <div class="bg-gray-50 rounded-xl p-3">
      <div class="text-xs text-gray-500">Montant de l’avoir</div>
      <div class="text-lg font-semibold">{{ $money($item->montant ?? $item->montant_ht ?? 0) }}</div>
    </div>
  </div>
@endif