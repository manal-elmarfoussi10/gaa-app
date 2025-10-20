@extends('layout')

@section('content')
<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Demandes de virement</h2>
    <form>
      <select name="status" class="border rounded px-2 py-1" onchange="this.form.submit()">
        @foreach($states as $s)
          <option value="{{ $s }}" @selected($current===$s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </form>
  </div>

  @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
  @endif

  <table class="w-full">
    <thead>
      <tr class="text-left border-b">
        <th class="py-2">Date</th>
        <th class="py-2">Société</th>
        <th class="py-2">Quantité</th>
        <th class="py-2">Montant HT</th>
        <th class="py-2">Statut</th>
        <th class="py-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($requests as $r)
        <tr class="border-b">
          <td class="py-2">{{ $r->created_at->format('d/m/Y H:i') }}</td>
          <td class="py-2">{{ $r->company?->name ?? '—' }}</td>
          <td class="py-2">{{ $r->quantity }}</td>
          <td class="py-2">{{ number_format($r->amount_ht ?? 0,2,',',' ') }} €</td>
          <td class="py-2">
            <span class="px-2 py-1 rounded text-xs
              @if($r->status==='pending') bg-amber-100 text-amber-700
              @elseif($r->status==='approved') bg-green-100 text-green-700
              @else bg-red-100 text-red-700 @endif">
              {{ ucfirst($r->status) }}
            </span>
          </td>
          <td class="py-2">
            <a href="{{ route('superadmin.virements.show',$r) }}" class="text-blue-600">Voir</a>
          </td>
        </tr>
      @empty
        <tr><td class="py-6 text-gray-500" colspan="6">Aucune demande.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="mt-4">{{ $requests->withQueryString()->links() }}</div>
</div>
@endsection