@php
    /** @var \App\Models\UnitPackage|null $package */
    $isEdit   = isset($package) && $package?->exists;
    $action   = $isEdit
        ? route('superadmin.units.packages.update', $package)
        : route('superadmin.units.packages.store');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700">Nom du pack (optionnel)</label>
        <input type="text" name="name"
               value="{{ old('name', $package->name ?? '') }}"
               class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Pack standard">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Prix par unité (HT)</label>
            <input type="number" step="0.01" min="0" name="price_ht"
                   value="{{ old('price_ht', $package->price_ht ?? '') }}"
                   class="mt-1 w-full border rounded-lg px-3 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre d’unités par pack</label>
            <input type="number" step="1" min="1" name="units"
                   value="{{ old('units', $package->units ?? 1) }}"
                   class="mt-1 w-full border rounded-lg px-3 py-2" required>
        </div>
    </div>

    {{-- Always send a value for is_active --}}
    <input type="hidden" name="is_active" value="0">
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1"
               @checked( old('is_active', (int)($package->is_active ?? 1)) )>
        <span class="text-sm text-gray-700">Activer ce tarif</span>
    </label>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-[#FF4B00] text-white px-4 py-2 rounded-lg hover:bg-orange-600">
            <i data-lucide="save" class="w-4 h-4"></i> Enregistrer
        </button>

        @if($isEdit)
            {{-- Désactiver / Supprimer --}}
            <form method="POST"
                  action="{{ route('superadmin.units.packages.destroy', $package) }}"
                  onsubmit="return confirm('Désactiver ce pack ?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Désactiver
                </button>
            </form>
        @endif
    </div>
</form>