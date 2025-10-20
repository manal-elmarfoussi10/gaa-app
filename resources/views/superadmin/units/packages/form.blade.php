@extends('layout')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-2xl shadow p-6 mt-10">
    <h1 class="text-2xl font-semibold mb-6 text-gray-800 flex items-center gap-2">
        <i data-lucide="layers" class="w-5 h-5 text-[#FF4B00]"></i>
        Paramètres du pack d’unités
    </h1>

    {{-- ✅ Success message --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- ⚠️ Validation errors --}}
    @if($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ==============================
         FORMULAIRE D’ENREGISTREMENT
    =============================== --}}
    <form method="POST" action="{{ route('superadmin.units.packages.store') }}">
        @csrf

        {{-- Nom optionnel --}}
        <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1">Nom du pack (optionnel)</label>
            <input type="text" name="name"
                   value="{{ old('name', $package->name ?? 'Pack standard') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]">
        </div>

        {{-- Prix par unité --}}
        <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1">Prix par unité (€)</label>
            <input type="number" name="price_per_unit" step="0.01" min="0"
                   value="{{ old('price_per_unit', $package->price_per_unit ?? 1) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]"
                   required>
        </div>

        {{-- Taux de TVA --}}
        <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1">TVA (%)</label>
            <input type="number" name="tax_rate" step="0.01" min="0" max="100"
                   value="{{ old('tax_rate', $package->tax_rate ?? 20) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]"
                   required>
        </div>

        {{-- Statut actif --}}
        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $package->is_active ?? true))
                       class="rounded border-gray-300 text-[#FF4B00] focus:ring-[#FF4B00]">
                <span class="ml-2 text-gray-700">Activer ce tarif</span>
            </label>
        </div>

        {{-- Boutons --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-[#FF4B00] text-white px-5 py-2 rounded-lg hover:bg-orange-600 transition">
                Enregistrer
            </button>

            {{-- Désactivation (DELETE) --}}
            @if(!empty($package))
            <form method="POST"
      action="{{ route('superadmin.units.packages.destroy', ['unit_package' => $p->getKey()]) }}"
      onsubmit="return confirm('Désactiver ce pack ?');">
    @csrf
    @method('DELETE')
    <button type="submit"
            class="inline-flex items-center gap-2 px-3 py-2 rounded border border-gray-300 hover:bg-gray-50">
        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer / Désactiver
    </button>
</form>
            @endif
        </div>
    </form>
</div>
@endsection