@extends('layout')

@section('content')
@php
    // Determine price to display:
    // - prefer stored unit_price on the request
    // - else fallback to active package
    $fallbackPrice = optional(\App\Models\UnitPackage::where('is_active', true)->first())->price_ht ?? 0;
    $unitPrice = $virement->unit_price ?? $fallbackPrice;
    $amountHt = $unitPrice * (int)$virement->quantity;
@endphp

<div class="max-w-5xl mx-auto mt-8 space-y-6">

    {{-- Header / breadcrumb-ish --}}
    <div class="bg-white rounded-2xl shadow p-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.virements.index') }}"
               class="hidden md:inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
            </a>
            <div class="w-10 h-10 rounded-xl bg-[#FFF1EC] flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5 text-[#FF4B00]"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-gray-800">
                    Demande #{{ $virement->id }}
                </h1>
                <p class="text-sm text-gray-500">Détails de la demande de virement et actions.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @php
                $badge = match($virement->status) {
                    'approved' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    default    => 'bg-amber-100 text-amber-700'
                };
                $icon  = match($virement->status) {
                    'approved' => 'check-circle',
                    'rejected' => 'x-circle',
                    default    => 'hourglass'
                };
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs {{ $badge }}">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                {{ ucfirst($virement->status) }}
            </span>

            @if($virement->proof_path)
                <a href="{{ route('superadmin.virements.proof', $virement) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                    <i data-lucide="paperclip" class="w-4 h-4"></i> Reçu
                </a>
            @endif
        </div>
    </div>

    {{-- Meta cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow p-5">
            <div class="text-xs uppercase tracking-wide text-gray-500">Société</div>
            <div class="mt-1 text-gray-900 font-semibold">{{ $virement->company?->name ?? '—' }}</div>
            <div class="mt-3 text-xs text-gray-500">Email demandeur</div>
            <div class="text-gray-800">{{ $virement->user?->email ?? '—' }}</div>
        </div>

        <div class="bg-white rounded-2xl shadow p-5">
            <div class="text-xs uppercase tracking-wide text-gray-500">Quantité demandée</div>
            <div class="mt-1 text-gray-900 font-semibold">{{ $virement->quantity }}</div>

            <div class="mt-3 text-xs text-gray-500">Prix unitaire (HT)</div>
            <div class="text-gray-900 font-semibold">
                {{ number_format($unitPrice, 2, ',', ' ') }} €
            </div>

            <div class="mt-3 text-xs text-gray-500">Montant HT estimé</div>
            <div class="text-gray-900 font-semibold">
                {{ number_format($amountHt, 2, ',', ' ') }} €
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-5">
            <div class="text-xs uppercase tracking-wide text-gray-500">Créée le</div>
            <div class="mt-1 text-gray-900 font-semibold">
                {{ $virement->created_at?->format('d/m/Y H:i') }}
            </div>
            <div class="mt-3 text-xs text-gray-500">Mise à jour</div>
            <div class="text-gray-900 font-semibold">
                {{ $virement->updated_at?->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    {{-- Proof preview --}}
    @php
        $proofUrl = $proofUrl ?? null;
    @endphp

    @if($virement->proof_path && $proofUrl)
        @php
            $ext = strtolower(pathinfo($virement->proof_path, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
            $isPdf   = $ext === 'pdf';
        @endphp

        <div class="mt-4 rounded-xl border border-dashed bg-gray-50">
            @if($isImage)
                <img src="{{ $proofUrl }}" alt="Reçu de virement" class="w-full h-auto rounded-xl">
            @elseif($isPdf)
                <object data="{{ $proofUrl }}" type="application/pdf" class="w-full h-[650px] rounded-xl">
                    <iframe src="{{ $proofUrl }}" class="w-full h-[650px] rounded-xl"></iframe>
                </object>
            @else
                <div class="w-full h-40 rounded-xl flex items-center justify-center text-gray-500 text-sm">
                    Aperçu non disponible — <a class="text-[#FF4B00] underline" href="{{ route('superadmin.virements.proof',$virement) }}">téléchargez le fichier</a>
                </div>
            @endif
        </div>
    @else
        <div class="mt-4 w-full h-40 rounded-xl bg-gray-50 border border-dashed flex items-center justify-center text-gray-500 text-sm">
            Aucun reçu fourni.
        </div>
    @endif

    {{-- Actions --}}
    @if($virement->status === 'pending')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Approve --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                        <i data-lucide="check" class="w-4 h-4 text-green-700"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Approuver & créditer</h3>
                </div>
                <form method="POST" action="{{ route('superadmin.virements.approve', $virement) }}" class="space-y-3">
                    @csrf
                    <label class="block text-sm text-gray-700">Unités à créditer</label>
                    <input type="number" min="1" name="credit_units"
                           value="{{ old('credit_units', $virement->quantity) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-200" required>

                    <label class="block text-sm text-gray-700">Note (optionnel)</label>
                    <textarea name="notes" rows="2"
                              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-200">{{ old('notes') }}</textarea>

                    <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Approuver
                    </button>
                </form>
            </div>

            {{-- Reject --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4 text-red-700"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Rejeter la demande</h3>
                </div>
                <form method="POST" action="{{ route('superadmin.virements.reject', $virement) }}" class="space-y-3">
                    @csrf
                    <label class="block text-sm text-gray-700">Raison</label>
                    <textarea name="notes" rows="3"
                              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200">{{ old('notes') }}</textarea>

                    <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        Rejeter
                    </button>
                </form>
            </div>
        </div>
    @else
        {{-- Read-only note when finished --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Note</h3>
            <div class="rounded-xl border bg-gray-50 px-4 py-3 text-sm text-gray-700">
                {{ $virement->notes ?: '—' }}
            </div>
        </div>
    @endif

    {{-- Footer action --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('superadmin.virements.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour aux virements
        </a>
        @if($virement->status==='pending')
            <div class="text-xs text-gray-500">
                Conseil : vérifiez bien le nom de la société et le montant du reçu avant d’approuver.
            </div>
        @endif
    </div>
</div>
@endsection