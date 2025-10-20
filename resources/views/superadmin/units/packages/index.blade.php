@extends('layout')

@section('content')
<div class="max-w-6xl mx-auto mt-8 space-y-6">

    {{-- header --}}
    <div class="bg-white rounded-2xl shadow p-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#FFF1EC] flex items-center justify-center">
                <i data-lucide="layers" class="w-5 h-5 text-[#FF4B00]"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-gray-800">Packs d’unités</h1>
                <p class="text-sm text-gray-500">Définissez le prix unitaire HT et visualisez le TTC appliqué aux achats d’unités.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.virements.index') }}"
               class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-sm">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                Virements
                @isset($pendingVirements)
                    @if($pendingVirements > 0)
                        <span class="ml-1 inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1.5 rounded-full text-xs bg-[#FF4B00] text-white">
                            {{ $pendingVirements }}
                        </span>
                    @endif
                @endisset
            </a>

            @if($packages->isEmpty())
                <a href="{{ route('superadmin.units.packages.create') }}"
                   class="inline-flex items-center gap-2 bg-[#FF4B00] text-white px-4 py-2 rounded-lg hover:bg-orange-600">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Créer le pack
                </a>
            @else
                <a href="{{ route('superadmin.units.packages.edit', $packages->first()) }}"
                   class="inline-flex items-center gap-2 bg-[#FF4B00] text-white px-4 py-2 rounded-lg hover:bg-orange-600">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                    Modifier
                </a>
            @endif
        </div>
    </div>

    {{-- flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 text-green-800 border border-green-200 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 text-red-800 border border-red-200 rounded-xl px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    @if($packages->isEmpty())
        {{-- empty state --}}
        <div class="bg-white rounded-2xl shadow p-10 text-center">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-[#FFF1EC] flex items-center justify-center">
                <i data-lucide="package-open" class="w-6 h-6 text-[#FF4B00]"></i>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-gray-800">Aucun pack configuré</h2>
            <p class="mt-1 text-gray-500">Créez votre premier pack pour activer l’achat d’unités par les sociétés.</p>
            <a href="{{ route('superadmin.units.packages.create') }}"
               class="mt-5 inline-flex items-center gap-2 bg-[#FF4B00] text-white px-5 py-2.5 rounded-lg hover:bg-orange-600">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Créer le pack
            </a>
        </div>
    @else
        @php
            $p   = $packages->first();
            $vat = 20; // fixed VAT for display
            $ttc = fn($q) => round($q * ($p->price_ht ?? 0) * (1 + $vat/100), 2);
            $ex  = [ 1 => $ttc(1), 10 => $ttc(10), 100 => $ttc(100) ];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- main card --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs
                            {{ $p->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-50 text-gray-600 border border-gray-200' }}">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $p->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ $p->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                            </span>
                            {{ $p->is_active ? 'Actif' : 'Inactif' }}
                        </div>

                        <h2 class="mt-3 text-xl font-semibold text-gray-800">
                            {{ $p->name ?? 'Pack standard' }}
                        </h2>
                        <p class="text-gray-500">Définit les tarifs appliqués à toutes les sociétés.</p>
                    </div>

                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ number_format($p->price_ht ?? 0, 2, ',', ' ') }} €
                        </div>
                        <div class="text-sm text-gray-500">par unité (HT)</div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">TVA</div>
                        <div class="text-lg font-semibold text-gray-800">{{ $vat }}%</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">Créé le</div>
                        <div class="text-lg font-semibold text-gray-800">{{ $p->created_at?->format('d/m/Y') }}</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">Dernière mise à jour</div>
                        <div class="text-lg font-semibold text-gray-800">{{ $p->updated_at?->format('d/m/Y') }}</div>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <a href="{{ route('superadmin.units.packages.edit', $p) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#FF4B00] text-white hover:bg-orange-600">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Modifier
                    </a>

                    <form method="POST"
                          action="{{ route('superadmin.units.packages.destroy', ['unit_package' => $p->getKey()]) }}"
                          onsubmit="return confirm('Désactiver ce pack ? Les achats resteront bloqués si aucun pack actif n’existe.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                            <i data-lucide="pause-circle" class="w-4 h-4"></i> Désactiver
                        </button>
                    </form>
                </div>
            </div>

            {{-- simulation / examples --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i data-lucide="calculator" class="w-4 h-4 text-[#FF4B00]"></i>
                    Exemples TTC
                </h3>

                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3">
                        <div class="text-gray-600">1 unité</div>
                        <div class="font-semibold text-gray-900">{{ number_format($ex[1], 2, ',', ' ') }} €</div>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3">
                        <div class="text-gray-600">10 unités</div>
                        <div class="font-semibold text-gray-900">{{ number_format($ex[10], 2, ',', ' ') }} €</div>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3">
                        <div class="text-gray-600">100 unités</div>
                        <div class="font-semibold text-gray-900">{{ number_format($ex[100], 2, ',', ' ') }} €</div>
                    </div>
                </div>

                <hr class="my-5">

                {{-- quick editor link --}}
                <a href="{{ route('superadmin.units.packages.edit', $p) }}"
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[#FFF1EC] text-[#FF4B00] hover:bg-[#FFE2D7]">
                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                    Ajuster les paramètres
                </a>
            </div>
        </div>
    @endif
</div>
@endsection