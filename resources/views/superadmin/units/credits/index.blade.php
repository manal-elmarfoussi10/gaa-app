@extends('layout')

@section('content')
<div class="max-w-7xl mx-auto mt-8 space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow p-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#FFF1EC] flex items-center justify-center">
                <i data-lucide="coins" class="w-5 h-5 text-[#FF4B00]"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-gray-800">Crédits d’unités</h1>
                <p class="text-sm text-gray-500">Historique des unités ajoutées aux sociétés (manuel, virement, ajustement).</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.units.credits.create') }}"
               class="inline-flex items-center gap-2 bg-[#FF4B00] text-white px-4 py-2 rounded-lg hover:bg-orange-600">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Créditer manuellement
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 text-green-800 border border-green-200 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-2xl shadow p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600">Société</label>
                <select name="company_id"
                        class="w-full mt-1 border rounded-lg px-3 py-2">
                    <option value="">Toutes</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" @selected(request('company_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-600">Source</label>
                <select name="source" class="w-full mt-1 border rounded-lg px-3 py-2">
                    <option value="">Toutes</option>
                    <option value="manual" @selected(request('source')==='manual')>Manuel</option>
                    <option value="virement" @selected(request('source')==='virement')>Virement</option>
                    <option value="adjustment" @selected(request('source')==='adjustment')>Ajustement</option>
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-600">Du</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="text-sm text-gray-600">Au</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
            </div>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border hover:bg-gray-50">
                <i data-lucide="filter" class="w-4 h-4"></i> Filtrer
            </button>
            <a href="{{ route('superadmin.units.credits.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border hover:bg-gray-50">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Réinitialiser
            </a>
        </div>
    </form>

    @if($credits->count() === 0)
        {{-- Empty state --}}
        <div class="bg-white rounded-2xl shadow p-10 text-center">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-[#FFF1EC] flex items-center justify-center">
                <i data-lucide="inbox" class="w-6 h-6 text-[#FF4B00]"></i>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-gray-800">Aucune ligne de crédit</h2>
            <p class="mt-1 text-gray-500">Créditez des unités à une société pour voir les écritures apparaître ici.</p>
            <a href="{{ route('superadmin.units.credits.create') }}"
               class="mt-5 inline-flex items-center gap-2 bg-[#FF4B00] text-white px-5 py-2.5 rounded-lg hover:bg-orange-600">
                <i data-lucide="plus" class="w-4 h-4"></i> Créditer manuellement
            </a>
        </div>
    @else
        {{-- Summary --}}
        <div class="bg-white rounded-2xl shadow p-5 flex flex-wrap items-center gap-4">
            <div class="rounded-xl border border-gray-200 px-4 py-3">
                <div class="text-xs text-gray-500">Crédits affichés</div>
                <div class="text-lg font-semibold text-gray-800">{{ $credits->total() }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 px-4 py-3">
                <div class="text-xs text-gray-500">Unités totales</div>
                <div class="text-lg font-semibold text-gray-800">
                    {{ number_format($credits->sum('units'), 0, ',', ' ') }}
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b bg-gray-50">
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Société</th>
                            <th class="px-4 py-3">Unités</th>
                            <th class="px-4 py-3">Source</th>
                            <th class="px-4 py-3">Créé par</th>
                            <th class="px-4 py-3">Lien</th>
                            <th class="px-4 py-3">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($credits as $line)
                            <tr class="border-b last:border-0">
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ $line->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $line->company?->name }}</div>
                                    <div class="text-xs text-gray-500">Solde: {{ $line->company?->units }} unités</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 font-semibold text-green-700">
                                        <i data-lucide="arrow-up-right" class="w-4 h-4"></i> +{{ $line->units }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($line->source){
                                            'virement'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'adjustment' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default      => 'bg-green-50 text-green-700 border-green-200',
                                        };
                                        $label = ucfirst($line->source ?? 'manuel');
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full border {{ $badge }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $line->author?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($line->virement_request_id)
                                        <a href="{{ route('superadmin.virements.show', $line->virement_request_id) }}"
                                           class="inline-flex items-center gap-1 text-blue-600 hover:underline">
                                            <i data-lucide="external-link" class="w-4 h-4"></i> Virement #{{ $line->virement_request_id }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700 max-w-[24rem]">
                                    <div class="truncate" title="{{ $line->note }}">{{ $line->note ?: '—' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t">
                {{ $credits->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection