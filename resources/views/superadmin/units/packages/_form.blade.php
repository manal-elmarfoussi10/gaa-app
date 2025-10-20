@php
    $isEdit = isset($package) && $package;
    $action = $isEdit
        ? route('superadmin.units.packages.update', ['unit_package' => $package->id])
        : route('superadmin.units.packages.store');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du pack (optionnel)</label>
        <input type="text" name="name" value="{{ old('name', $package->name ?? '') }}"
               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Prix par unité HT (€)</label>
        <input type="number" step="0.01" min="0" name="price_ht"
               value="{{ old('price_ht', $package->price_ht ?? 0) }}"
               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-300" required>
        <p class="text-xs text-gray-500 mt-1">TVA 20% sera appliquée lors de l’achat.</p>
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

        @if($isEdit)
            <form method="POST"
                  action="{{ route('superadmin.units.packages.destroy', ['unit_package' => $package->id]) }}"
                  onsubmit="return confirm('Désactiver ce pack ?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                    <i data-lucide="pause-circle" class="w-4 h-4"></i> Supprimer / Désactiver
                </button>
            </form>
        @endif
    </div>
</form>