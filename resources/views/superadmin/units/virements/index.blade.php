@extends('layout')

@section('content')
@php
    // Try to get a unit price for display/calculation:
    // - Prefer a value injected by the controller ($unitPrice), else
    // - Fall back to the active package price, else 0.
    $unitPrice = $unitPrice
        ?? optional(\App\Models\UnitPackage::where('is_active', true)->first())->price_ht
        ?? 0;
@endphp

<div class="max-w-7xl mx-auto mt-8 space-y-6">

    {{-- Header card --}}
    <div class="bg-white rounded-2xl shadow p-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#FFF1EC] flex items-center justify-center">
                <i data-lucide="credit-card" class="w-5 h-5 text-[#FF4B00]"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-gray-800">Demandes de virement</h1>
                <p class="text-sm text-gray-500">Validez les reçus envoyés par les sociétés et créditez leurs unités.</p>
            </div>
        </div>

        {{-- Quick status filter (desktop) --}}
        <form method="GET" class="hidden md:flex items-center gap-2">
            <label class="text-sm text-gray-600">Statut</label>
            <select name="status"
                    class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF4B00]/30"
                    onchange="this.form.submit()">
                @foreach($states as $s)
                    <option value="{{ $s }}" @selected($current===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Flash messages --}}
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

    {{-- Filter bar (mobile) --}}
    <div class="md:hidden">
        <form method="GET" class="bg-white rounded-2xl shadow p-4 flex items-center justify-between">
            <label class="text-sm text-gray-600">Statut</label>
            <select name="status"
                    class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF4B00]/30"
                    onchange="this.form.submit()">
                @foreach($states as $s)
                    <option value="{{ $s }}" @selected($current===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    @if($current === 'all')
                        Toutes les demandes
                    @else
                        Statut : <span class="font-medium text-gray-800">{{ ucfirst($current) }}</span>
                    @endif
                </div>
                <div class="text-sm text-gray-500">
                    Total : <span class="font-medium text-gray-800">{{ $requests->total() }}</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left bg-gray-50 text-gray-600 border-b">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Société</th>
                        <th class="px-6 py-3">Quantité</th>
                        <th class="px-6 py-3">Montant HT</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($requests as $r)
                        @php
                            // Prefer a per-request stored price, else fallback to global active price
                            $price = $r->unit_price ?? $unitPrice;
                            $amountHt = $price * (int)$r->quantity;
                        @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-6 py-3 whitespace-nowrap text-gray-800">
                                {{ $r->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <i data-lucide="building-2" class="w-4 h-4 text-gray-500"></i>
                                    </div>
                                    <div class="text-gray-800">{{ $r->company?->name ?? '—' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-800">{{ $r->quantity }}</td>
                            <td class="px-6 py-3 text-gray-800">
                                {{ number_format($amountHt, 2, ',', ' ') }} €
                                <span class="text-xs text-gray-500">( {{ number_format($price, 2, ',', ' ') }} € / u )</span>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $badge = match($r->status) {
                                        'approved' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default    => 'bg-amber-100 text-amber-700'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs {{ $badge }}">
                                    @if($r->status==='approved')
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    @elseif($r->status==='rejected')
                                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                    @else
                                        <i data-lucide="hourglass" class="w-3.5 h-3.5"></i>
                                    @endif
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Voir --}}
                                    <a href="{{ route('superadmin.virements.show', $r) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                        Voir
                                    </a>

                                    {{-- Approve / Reject only when pending --}}
                                    @if($r->status === 'pending')
                                        <form method="POST" action="{{ route('superadmin.virements.approve', $r) }}">
                                            @csrf
                                            {{-- controller expects credit_units --}}
                                            <input type="hidden" name="credit_units" value="{{ (int)$r->quantity }}">
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                                Approuver
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('superadmin.virements.reject', $r) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                                Refuser
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10">
                                <div class="text-center">
                                    <div class="mx-auto w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center">
                                        <i data-lucide="inbox" class="w-6 h-6 text-gray-400"></i>
                                    </div>
                                    <p class="mt-3 text-gray-700 font-medium">Aucune demande</p>
                                    <p class="text-gray-500 text-sm">Aucune demande de virement pour ce filtre.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t">
            {{ $requests->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection