@extends('layout')

@section('content')
<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Packs d’unités</h2>
    <a href="{{ route('superadmin.units.packages.create') }}" class="px-3 py-2 bg-orange-500 text-white rounded">Nouveau pack</a>
  </div>

  @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
  @endif

  <table class="w-full">
    <thead>
      <tr class="text-left border-b">
        <th class="py-2">Nom</th>
        <th class="py-2">Unités</th>
        <th class="py-2">Prix HT</th>
        <th class="py-2">Prix TTC</th>
        <th class="py-2">Actif</th>
        <th class="py-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($packages as $p)
        <tr class="border-b">
          <td class="py-2">{{ $p->name }}</td>
          <td class="py-2">{{ $p->units }}</td>
          <td class="py-2">{{ number_format($p->price_ht,2,',',' ') }} €</td>
          <td class="py-2">{{ number_format($p->price_ttc,2,',',' ') }} €</td>
          <td class="py-2">
            <span class="px-2 py-1 rounded text-xs {{ $p->is_active?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">
              {{ $p->is_active ? 'Oui' : 'Non' }}
            </span>
          </td>
          <td class="py-2 space-x-2">
            <a href="{{ route('superadmin.units.packages.edit',$p) }}" class="text-blue-600">Éditer</a>
            <form action="{{ route('superadmin.units.packages.destroy',$p) }}" method="POST" class="inline"
                  onsubmit="return confirm('Supprimer ce pack ?')">
              @csrf @method('DELETE')
              <button class="text-red-600">Supprimer</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td class="py-6 text-gray-500" colspan="6">Aucun pack.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection