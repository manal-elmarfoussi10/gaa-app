@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
  <h2 class="text-xl font-semibold mb-4">Demande #{{ $virement->id }}</h2>

  <dl class="grid grid-cols-2 gap-3 mb-6">
    <div><dt class="text-xs text-gray-500">Société</dt><dd class="font-medium">{{ $virement->company?->name ?? '—' }}</dd></div>
    <div><dt class="text-xs text-gray-500">Email demandeur</dt><dd class="font-medium">{{ $virement->user?->email ?? '—' }}</dd></div>
    <div><dt class="text-xs text-gray-500">Quantité demandée</dt><dd class="font-medium">{{ $virement->quantity }}</dd></div>
    <div><dt class="text-xs text-gray-500">Montant HT</dt><dd class="font-medium">{{ number_format($virement->amount_ht ?? 0,2,',',' ') }} €</dd></div>
    <div><dt class="text-xs text-gray-500">Statut</dt><dd class="font-medium">{{ ucfirst($virement->status) }}</dd></div>
  </dl>

  @if($virement->proof_path)
    <a href="{{ route('superadmin.virements.proof',$virement) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 rounded border mb-4">Télécharger le reçu</a>
  @endif

  @if($virement->status==='pending')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <form method="POST" action="{{ route('superadmin.virements.approve',$virement) }}" class="bg-green-50 p-4 rounded border">
        @csrf
        <h3 class="font-semibold mb-2">Approuver & créditer</h3>
        <label class="block text-sm">Unités à créditer</label>
        <input type="number" min="1" name="credit_units" value="{{ old('credit_units',$virement->quantity) }}" class="w-full border rounded px-3 py-2 mb-2" required>
        <label class="block text-sm">Note (optionnel)</label>
        <textarea name="notes" class="w-full border rounded px-3 py-2 mb-3" rows="2">{{ old('notes') }}</textarea>
        <button class="px-3 py-2 bg-green-600 text-white rounded">Approuver</button>
      </form>

      <form method="POST" action="{{ route('superadmin.virements.reject',$virement) }}" class="bg-red-50 p-4 rounded border">
        @csrf
        <h3 class="font-semibold mb-2">Rejeter</h3>
        <label class="block text-sm">Raison</label>
        <textarea name="notes" class="w-full border rounded px-3 py-2 mb-3" rows="3">{{ old('notes') }}</textarea>
        <button class="px-3 py-2 bg-red-600 text-white rounded">Rejeter</button>
      </form>
    </div>
  @else
    <div class="bg-gray-50 p-3 rounded border mt-4">
      <div class="text-sm text-gray-600">Note : {{ $virement->notes ?: '—' }}</div>
    </div>
  @endif

  <div class="mt-6">
    <a href="{{ route('superadmin.virements.index') }}" class="px-3 py-2 border rounded">Retour</a>
  </div>
</div>
@endsection