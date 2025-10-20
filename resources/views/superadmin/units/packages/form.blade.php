@extends('layout')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
  <h2 class="text-xl font-semibold mb-4">
    {{ $package->exists ? 'Modifier le pack' : 'Nouveau pack' }}
  </h2>

  <form method="POST" action="{{ $package->exists ? route('superadmin.units.packages.update',$package) : route('superadmin.units.packages.store') }}">
    @csrf
    @if($package->exists) @method('PUT') @endif

    <label class="block text-sm mt-2">Nom</label>
    <input name="name" class="w-full border rounded px-3 py-2" value="{{ old('name',$package->name) }}" required>

    <label class="block text-sm mt-4">Unités</label>
    <input type="number" min="1" name="units" class="w-full border rounded px-3 py-2" value="{{ old('units',$package->units) }}" required>

    <label class="block text-sm mt-4">Prix HT (€)</label>
    <input type="number" step="0.01" min="0" name="price_ht" class="w-full border rounded px-3 py-2" value="{{ old('price_ht',$package->price_ht) }}" required>

    <label class="inline-flex items-center mt-4">
      <input type="checkbox" name="is_active" value="1" class="mr-2" {{ old('is_active',$package->is_active) ? 'checked' : '' }}>
      Actif
    </label>

    <div class="mt-6 flex gap-2">
      <button class="px-4 py-2 bg-orange-500 text-white rounded">{{ $package->exists ? 'Mettre à jour' : 'Créer' }}</button>
      <a href="{{ route('superadmin.units.packages.index') }}" class="px-4 py-2 border rounded">Annuler</a>
    </div>
  </form>
</div>
@endsection