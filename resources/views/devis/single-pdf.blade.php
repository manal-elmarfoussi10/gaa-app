<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis #{{ $devis->numero ?? $devis->id }}</title>
    <style>
        body {
            font-family:'Segoe UI',sans-serif;
            font-size:11px;
            color:#1f2937;
            margin:0;
            padding:18px 28px;
        }

        /* HEADER */
        .top-line {
            display: table;
            width: 100%;
        }
        .top-left,
        .top-right {
            display: table-cell;
            vertical-align: top;
        }
        .top-right {
            text-align: right;
        }

        .company-info {
            font-size:11px;
            line-height:1.4;
        }
        .devis-info {
            font-size:12px;
        }
        .devis-title {
            font-size:20px;
            color:#0ea5e9;
            font-weight:bold;
        }

        /* CLIENT */
        .client-info {
            margin:12px 0 14px;
            font-size:11px;
            line-height:1.4;
        }

        /* TITLES */
        .section-title {
            font-weight:700;
            margin:10px 0 4px;
            color:#0f172a;
            text-transform:uppercase;
            font-size:12px;
        }

        /* VEHICLE BLOCK — compact version */
        .vs-line {
            margin:1px 0;
            font-size:11px;
        }

        /* ITEMS TABLE */
        table.items {
            width:100%;
            border-collapse:collapse;
            font-size:11px;
            margin-top:8px;
        }
        table.items th,
        table.items td {
            border-bottom:1px solid #e5e7eb;
            padding:4px;
            text-align:left;
        }
        table.items th {
            background:#e0f2fe;
            font-weight:700;
            color:#0c4a6e;
        }

        /* TOTALS */
        .totals {
            width:45%;
            margin-left:auto;
            margin-top:8px;
            border-collapse:collapse;
            page-break-inside: avoid;
        }
        .totals td {
            padding:4px 2px;
        }

        /* BOTTOM (MODALITES + SIGNATURE) */
        table.bottom-table {
            width:100%;
            margin-top:12px;
            border-collapse:collapse;
            page-break-inside: avoid;
        }
        table.bottom-table td {
            vertical-align:top;
            font-size:10.5px;
            padding:0 4px;
        }
        .bottom-left,
        .bottom-right {
            width:50%;
        }
        .bottom-table .section-title {
            margin-top:0;
            margin-bottom:6px;
            font-size:11.5px;
        }
        .sig-box {
            margin-top:18px;
            text-align:center;
            border-top:1px dashed #94a3b8;
            padding-top:8px;
            font-size:11px;
        }

        /* FOOTER */
        .footer {
            font-size:10px;
            margin-top:14px;
            text-align:center;
            border-top:1px solid #e2e8f0;
            padding-top:6px;
            color:#64748b;
        }
    </style>
</head>
<body>

@php
    $client = $devis->client;

    // Nom affiché
    $displayName = $client
        ? trim(($client->prenom ? $client->prenom.' ' : '').($client->nom_assure ?? ''))
        : ($devis->prospect_name ?? '—');

    // Helpers
    $fmtDate = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : null;
    $fmtKm   = fn($v) => filled($v) ? number_format((int)$v, 0, ',', ' ').' km' : null;
@endphp

{{-- HEADER --}}
<div class="top-line">
    <div class="top-left">
        <div class="company-info">
            @if($company)
                <strong>{{ $company->commercial_name ?? $company->name }}</strong><br>
                {{ $company->address }}<br>
                {{ $company->postal_code }} {{ $company->city }}<br>
                {{ $company->email }}<br>
                {{ $company->phone }}
            @endif
        </div>
    </div>

    <div class="top-right">
        <div class="devis-info">
            <div class="devis-title">DEVIS</div>
            <div>#{{ $devis->numero ?? $devis->id }}</div>
            <div>Le {{ \Carbon\Carbon::parse($devis->date_devis)->format('d/m/Y') }}</div>
        </div>
    </div>
</div>

{{-- CLIENT --}}
<div class="client-info">
    <strong>{{ $displayName }}</strong><br>

    @if($client)
        @if($client->adresse)
            {{ $client->adresse }}<br>
        @endif
        @if(($client->code_postal ?? '') || ($client->ville ?? ''))
            {{ $client->code_postal }} {{ $client->ville }}<br>
        @endif
        @if($client->email)
            Email : {{ $client->email }}<br>
        @endif
        @if($client->telephone)
            Tél. : {{ $client->telephone }}<br>
        @endif
    @else
        @if($devis->prospect_email)
            {{ $devis->prospect_email }}<br>
        @endif
        @if($devis->prospect_phone)
            {{ $devis->prospect_phone }}<br>
        @endif
    @endif
</div>

{{-- VÉHICULE / SINISTRE / ASSURANCE --}}
@if($client)
    <div class="section-title">Véhicule / Sinistre / Assurance</div>

    @php
        $l1 = [];
        if($client->plaque)           $l1[] = "Plaque : ".$client->plaque;
        if($client->kilometrage)      $l1[] = "Kilométrage : ".$fmtKm($client->kilometrage);
        if($client->ancien_modele_plaque) $l1[] = "Ancien modèle : Oui";
        if($client->professionnel)    $l1[] = "Professionnel : ".$client->professionnel;

        $l2 = [];
        if($client->type_vitrage)     $l2[] = "Vitrage : ".$client->type_vitrage;
        if(!is_null($client->reparation))
            $l2[] = "Réparation : ".($client->reparation ? 'Oui' : 'Non');
        if($client->numero_police)    $l2[] = "Police : ".$client->numero_police;
        if($client->numero_sinistre)  $l2[] = "N° sinistre : ".$client->numero_sinistre;

        $l3 = [];
        if($client->nom_assurance)    $l3[] = "Assurance : ".$client->nom_assurance;
        if($client->autre_assurance)  $l3[] = "Autre : ".$client->autre_assurance;
        if($client->date_sinistre)    $l3[] = "Date sinistre : ".$fmtDate($client->date_sinistre);
        if($client->date_declaration) $l3[] = "Déclaration : ".$fmtDate($client->date_declaration);
    @endphp

    @if(count($l1))
        <div class="vs-line">{{ implode(' / ', $l1) }}</div>
    @endif
    @if(count($l2))
        <div class="vs-line">{{ implode(' / ', $l2) }}</div>
    @endif
    @if(count($l3))
        <div class="vs-line">{{ implode(' / ', $l3) }}</div>
    @endif

    @if($client->adresse_pose)
        <div class="vs-line">Adresse de pose : {{ $client->adresse_pose }}</div>
    @endif
@endif

{{-- DÉTAILS DES PRESTATIONS --}}
<div class="section-title">Détails des prestations</div>

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
                {{ $item->produit }}
                @if($item->description)
                    <br><span style="color:#6b7280;">{{ $item->description }}</span>
                @endif
            </td>
            <td>{{ number_format($item->prix_unitaire, 2, ',', ' ') }} €</td>
            <td>{{ rtrim(rtrim(number_format((float)$item->quantite, 2, ',', ' '), '0'), ',') }}</td>
            <td>{{ number_format($item->total_ht, 2, ',', ' ') }} €</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- TOTALS --}}
<table class="totals">
    <tr>
        <td>Total HT</td>
        <td style="text-align:right;">
            {{ number_format($devis->total_ht, 2, ',', ' ') }} €
        </td>
    </tr>
    <tr>
        <td>TVA (20%)</td>
        <td style="text-align:right;">
            {{ number_format($devis->total_tva, 2, ',', ' ') }} €
        </td>
    </tr>
    <tr>
        <td><strong>Total TTC</strong></td>
        <td style="text-align:right;">
            <strong>{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</strong>
        </td>
    </tr>
</table>
<br><br>
{{-- MODALITÉS + SIGNATURE SUR LA MÊME LIGNE --}}
<table class="bottom-table">
    <tr>
        <td class="bottom-left">
            <div class="section-title">Modalités & conditions de règlement</div>
            <p>Règlement : Virement bancaire ou chèque à l'ordre de {{ $company->commercial_name ?? $company->name }}.</p>

            @if($company->bic)
                <p>Code B.I.C : {{ $company->bic }}</p>
            @endif
            @if($company->iban)
                <p>Code I.B.A.N : {{ $company->iban }}</p>
            @endif

            <p>
                Passé ce délai, une pénalité pourra être appliquée conformément au Code du commerce.
                Une indemnité forfaitaire de 40€ pour frais de recouvrement peut être exigible.
            </p>
        </td>

        <td class="bottom-right">
            <div class="section-title">Signature</div>
            <div class="sig-box">
                Bon pour accord<br>
                {{ $company->commercial_name ?? $company->name }}
            </div>
        </td>
    </tr>
</table>

{{-- FOOTER --}}
<div class="footer">
    {{ $company->commercial_name ?? $company->name }}
    @if($company?->siret) — SIRET : {{ $company->siret }} @endif
    @if($company?->tva)   — TVA : {{ $company->tva }} @endif
    @if($company?->ape)   — APE : {{ $company->ape }} @endif
    @if($company?->rcs_number) — RCS : {{ $company->rcs_number }} {{ $company->rcs_city }} @endif
</div>

</body>
</html>