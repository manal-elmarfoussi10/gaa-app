@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto mt-8">
    <div class="bg-white rounded-2xl shadow p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                <i data-lucide="layers" class="w-5 h-5 text-[#FF4B00]"></i>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">
                Paramètres du pack d’unités
            </h1>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                <div class="font-semibold mb-1">Veuillez corriger les erreurs :</div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ isset($package) ? route('superadmin.units.packages.update', ['unit_package' => $package->id]) : route('superadmin.units.packages.store') }}"
            class="space-y-5"
        >
            @csrf
            @if(isset($package))
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du pack (optionnel)</label>
                <input type="text" name="name" value="{{ old('name', $package->name ?? '') }}"
                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix par unité (€)</label>
                <input type="number" step="0.01" min="0" name="unit_price" value="{{ old('unit_price', $package->unit_price ?? 1) }}"
                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-300" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">TVA (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="vat_rate" value="{{ old('vat_rate', $package->vat_rate ?? 20) }}"
                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-300" required>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
                <label for="is_active" class="text-sm text-gray-700">Activer ce tarif</label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#FF4B00] text-white hover:bg-orange-600 transition">
                    <i data-lucide="save" class="w-4 h-4"></i> Enregistrer
                </button>

                {{-- Delete / deactivate: only when editing an existing record --}}
                @if(isset($package))
                    <form method="POST"
                          action="{{ route('superadmin.units.packages.destroy', ['unit_package' => $package->id]) }}"
                          onsubmit="return confirm('Désactiver ce pack ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                            <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer / Désactiver
                        </button>
                    </form>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection