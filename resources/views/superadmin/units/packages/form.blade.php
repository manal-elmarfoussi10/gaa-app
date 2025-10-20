@extends('layout')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-xl p-6 shadow">
    <h1 class="text-xl font-semibold mb-4">Paramètres des unités</h1>

    @if(session('success'))
        <div class="mb-3 rounded bg-green-100 text-green-800 px-3 py-2">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-3 rounded bg-red-100 text-red-800 px-3 py-2">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.units.packages.store') }}">
        @csrf
        <div class="mb-3">
            <label class="block text-sm text-gray-600">Nom (optionnel)</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2"
                   value="{{ old('name', $package->name ?? 'Tarif standard') }}">
        </div>

        <div class="mb-3">
            <label class="block text-sm text-gray-600">Prix par unité (€)</label>
            <input type="number" step="0.01" min="0" name="price_per_unit"
                   class="w-full border rounded px-3 py-2"
                   value="{{ old('price_per_unit', $package->price_per_unit ?? 10) }}" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm text-gray-600">TVA (%)</label>
            <input type="number" step="0.01" min="0" max="100" name="tax_rate"
                   class="w-full border rounded px-3 py-2"
                   value="{{ old('tax_rate', $package->tax_rate ?? 20) }}" required>
        </div>

        <label class="inline-flex items-center gap-2 mb-4">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $package->is_active ?? true))>
            <span>Activer ce tarif</span>
        </label>

        <div class="mt-4 flex items-center gap-3">
            {{-- Save --}}
            <button type="submit"
                    class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                Enregistrer
            </button>
        
            @if($package)
                {{-- Deactivate (uses DELETE on destroy route with ID) --}}
                <form method="POST"
                      action="{{ route('superadmin.units.packages.destroy', $package) }}"
                      onsubmit="return confirm('Désactiver ce tarif ?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="px-4 py-2 rounded border">
                        Désactiver
                    </button>
                </form>
            @endif
        </div>
    </form>
</div>
@endsection