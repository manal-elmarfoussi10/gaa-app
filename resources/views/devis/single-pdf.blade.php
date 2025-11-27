<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis #{{ $devis->numero ?? $devis->id }}</title>

    <style>
        body { font-family:'Segoe UI',sans-serif; font-size:12px; color:#1f2937; margin:0; padding:24px 36px; background:#fff; }

        .header { display:flex; justify-content:space-between; border-bottom:1px solid #e2e8f0; padding-bottom:12px; margin-bottom:20px; }
        .company-info { font-size:12px; line-height:1.5; }
        .devis-info { text-align:right; }
        .devis-info h2 { margin:0; font-size:22px; color:#0ea5e9; letter-spacing:.5px; }

        .client-info { margin:16px 0 16px; font-size:12px; line-height:1.55; }

        .section-title { font-weight:700; margin:18px 0 8px; color:#0f172a; text-transform:uppercase; font-size:13px; }

        table.items { width:100%; border-collapse:collapse; font-size:12px; margin-top:14px; }
        table.items th, table.items td { border-bottom:1px solid #e5e7eb; padding:8px; text-align:left; }
        table.items th { background:#e0f2fe; font-weight:700; color:#0c4a6e; }

        .totals { width:50%; margin-left:auto; margin-top:16px; border-collapse:collapse; }
        .totals td { padding:6px 8px; }

        .compact-block { font-size:12px; line-height:1.45; margin-bottom:14px; }
        .compact-block p { margin:3px 0; }

        .signature { margin-top:40px; display:flex; justify-content:space-between; }
        .signature div { width:45%; text-align:center; border-top:1px dashed #94a3b8; padding-top:10px; font-size:12px; }

        .payment-block { font-size:12px; line-height:1.45; margin-top:16px; }

        .footer { font-size:11px; margin-top:28px; text-align:center; border-top:1px solid #e2e8f0; padding-top:10px; color:#64748b; }

        .muted { color:#6b7280; }
    </style>
</head>

<body>

@php
    $client = $devis->client;

    $displayName = $client
        ? trim(($client->prenom ? $client->prenom.' ' : '').($client->nom_assure ?? ''))
        : ($devis->prospect_name ?? '—');

    $addr1 = $client?->adresse;
    $addr2 = trim(($client->code_postal ?? '').' '.($client->ville ?? ''));

    $fmtDate = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : null;
    $fmtKm = fn($v) => filled($v) ? number_format((int)$v, 0, ',', ' ').' km' : null;
@endphp

<!-- EN-TÊTE -->
<div class="header">
    <div class="company-info">
        <strong>{{ $company->commercial_name ?? $company->name }}</strong><br>
        {{ $company->address }}<br>
        {{ $company->postal_code }} {{ $company->city }}<br>
        {{ $company->email }}<br>
        {{ $company->phone }}
    </div>
    <div class="devis-info">
        <h2>DEVIS</h2>
        <p>#{{ $devis->numero ?? $devis->id }}</p>
        <p>{{ $company->city ?? '' }}, le {{ \Carbon\Carbon::parse($devis->date_devis)->format('d/m/Y') }}</p>
    </div>
</div>

<!-- CLIENT -->
<div class="client-info">
    <strong>{{ $displayName }}</strong><br>
    @if($client)
        @if($addr1) {{ $addr1 }}<br>@endif
        @if($addr2) {{ $addr2 }}<br>@endif
        @if($client->email) Email : {{ $client->email }}<br>@endif
        @if($client->telephone) Tél. : {{ $client->telephone }}<br>@endif
    @else
        @if($devis->prospect_email) Email : {{ $devis->prospect_email }}<br>@endif
        @if($devis->prospect_phone) Tél. : {{ $devis->prospect_phone }}<br>@endif
    @endif
</div>

<!-- VÉHICULE / SINISTRE / ASSURANCE (compact + seulement infos existantes) -->
@if($client)
    <div class="section-title">VÉHICULE / SINISTRE / ASSURANCE</div>
    <div class="compact-block">

        {{-- Ligne 1 --}}
        @php
            $line1 = [];
            if(filled($client->plaque)) {
                $line1[] = '<strong>Plaque :</strong> '.e($client->plaque);
            }
            if($fmtKm($client->kilometrage)) {
                $line1[] = '<strong>Kilométrage :</strong> '.$fmtKm($client->kilometrage);
            }
            if(!is_null($client->ancien_modele_plaque)) {
                $line1[] = '<strong>Ancien modèle :</strong> '.($client->ancien_modele_plaque ? 'Oui' : 'Non');
            }
            if(filled($client->professionnel)) {
                $line1[] = '<strong>Professionnel :</strong> '.e($client->professionnel);
            }
        @endphp
        @if(count($line1))
            <p>{!! implode(' / ', $line1) !!}</p>
        @endif

        {{-- Ligne 2 --}}
        @php
            $line2 = [];
            if(filled($client->type_vitrage)) {
                $line2[] = '<strong>Vitrage :</strong> '.e($client->type_vitrage);
            }
            if(!is_null($client->reparation)) {
                $line2[] = '<strong>Réparation :</strong> '.($client->reparation ? 'Oui' : 'Non');
            }
            if(filled($client->numero_police)) {
                $line2[] = '<strong>Police :</strong> '.e($client->numero_police);
            }
            if(filled($client->numero_sinistre)) {
                $line2[] = '<strong>N° sinistre :</strong> '.e($client->numero_sinistre);
            }
        @endphp
        @if(count($line2))
            <p>{!! implode(' / ', $line2) !!}</p>
        @endif

        {{-- Ligne 3 --}}
        @php
            $line3 = [];
            if(filled($client->nom_assurance)) {
                $line3[] = '<strong>Assurance :</strong> '.e($client->nom_assurance);
            }
            if(filled($client->autre_assurance)) {
                $line3[] = '<strong>Autre :</strong> '.e($client->autre_assurance);
            }
            if($fmtDate($client->date_sinistre)) {
                $line3[] = '<strong>Date sinistre :</strong> '.$fmtDate($client->date_sinistre);
            }
            if($fmtDate($client->date_declaration)) {
                $line3[] = '<strong>Déclaration :</strong> '.$fmtDate($client->date_declaration);
            }
        @endphp
        @if(count($line3))
            <p>{!! implode(' / ', $line3) !!}</p>
        @endif

        {{-- Adresse de pose uniquement si présente --}}
        @if(filled($client->adresse_pose))
            <p><strong>Adresse de pose :</strong> {{ $client->adresse_pose }}</p>
        @endif
    </div>
@endif

<!-- DÉTAILS DES PRESTATIONS -->
<div class="section-title">DÉTAILS DES PRESTATIONS</div>
<table class="items">
    <thead>
    <tr>
        <th>Description</th>
        <th>Prix unitaire</th>
        <th>Qté</th>
        <th>Montant HT</th>
    </tr>
    </thead>
    <tbody>
    @foreach($devis->items as $item)
        <tr>
            <td>
                <div>{{ $item->produit }}</div>
                @if($item->description)
                    <div class="muted">{{ $item->description }}</div>
                @endif
            </td>
            <td>{{ number_format($item->prix_unitaire, 2, ',', ' ') }} €</td>
            <td>{{ rtrim(rtrim(number_format((float)$item->quantite, 2, ',', ' '), '0'), ',') }}</td>
            <td>{{ number_format($item->total_ht, 2, ',', ' ') }} €</td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- TOTALS -->
<table class="totals">
    <tr>
        <td>Total HT</td>
        <td style="text-align:right;">{{ number_format($devis->total_ht, 2, ',', ' ') }} €</td>
    </tr>
    <tr>
        <td>TVA (20%)</td>
        <td style="text-align:right;">{{ number_format($devis->total_tva, 2, ',', ' ') }} €</td>
    </tr>
    <tr>
        <td><strong>Total TTC</strong></td>
        <td style="text-align:right;"><strong>{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</strong></td>
    </tr>
</table>

<!-- MODALITÉS & CONDITIONS DE RÈGLEMENT -->
<div class="section-title">MODALITÉS & CONDITIONS DE RÈGLEMENT</div>

<div class="payment-block">
    <p><strong>Règlement :</strong> Virement bancaire ou chèque à l'ordre de {{ $company->commercial_name ?? $company->name }}.</p>

    @if($company->bic)
        <p><strong>Code B.I.C :</strong> {{ $company->bic }}</p>
    @endif

    @if($company->iban)
        <p><strong>Code I.B.A.N :</strong> {{ $company->iban }}</p>
    @endif

    @if($devis->date_validite)
        <p style="margin-top:6px;">
            La présente facture sera payable au plus tard le : <strong>{{ $fmtDate($devis->date_validite) }}</strong>.
        </p>
    @endif

    <p class="muted" style="margin-top:4px;">
        Passé ce délai, sans obligation d’envoi d’une relance, une pénalité pourra être appliquée conformément au Code de commerce.
        Une indemnité forfaitaire de 40 € pour frais de recouvrement peut également être exigible.
    </p>
</div>

<!-- SIGNATURE -->
<div class="signature">
    <div>Bon pour accord</div>
    <div>{{ $company->commercial_name ?? $company->name }}</div>
</div>

<!-- PIED DE PAGE -->
<div class="footer">
    {{ $company->commercial_name ?? $company->name }}
    @if($company?->siret) — SIRET: {{ $company->siret }} @endif
    @if($company?->tva) — TVA: {{ $company->tva }} @endif
    @if($company?->ape) — Code APE: {{ $company->ape }} @endif
    @if($company?->rcs_number) — RCS: {{ $company->rcs_number }} {{ $company->rcs_city }} @endif
</div>

</body>
</html>