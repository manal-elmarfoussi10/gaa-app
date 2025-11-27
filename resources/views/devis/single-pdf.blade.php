<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis #{{ $devis->numero ?? $devis->id }}</title>
    <style>
        body {
            font-family:'Segoe UI',sans-serif;
            font-size:12px;
            color:#1f2937;
            margin:0;
            padding:24px 36px;
        }

        /* HEADER */
        .top-line {
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            width:100%;
        }
        .company-info { font-size:12px; line-height:1.5; }
        .devis-info { text-align:right; font-size:13px; }
        .devis-info h2 { margin:0; font-size:22px; color:#0ea5e9; }

        /* CLIENT */
        .client-info { margin:14px 0 18px; font-size:12px; line-height:1.45; }

        /* TITLES */
        .section-title {
            font-weight:700;
            margin:14px 0 6px;
            color:#0f172a;
            text-transform:uppercase;
            font-size:13px;
        }

        /* VEHICLE BLOCK — compact version */
        .vs-line { margin:2px 0; font-size:12px; }

        /* ITEMS TABLE */
        table.items {
            width:100%;
            border-collapse:collapse;
            font-size:12px;
            margin-top:10px;
        }
        table.items th, table.items td {
            border-bottom:1px solid #e5e7eb;
            padding:6px;
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
            margin-top:12px;
            border-collapse:collapse;
        }
        .totals td { padding:6px 4px; }

        /* FOOTER BLOCKS */
        .bottom-line {
            display:flex;
            justify-content:space-between;
            width:100%;
            margin-top:20px;
        }
        .modalites, .signature {
            width:48%;
            font-size:11.5px;
            line-height:1.45;
        }
        .signature .box {
            margin-top:20px;
            text-align:center;
            border-top:1px dashed #94a3b8;
            padding-top:10px;
        }

        .footer {
            font-size:11px;
            margin-top:18px;
            text-align:center;
            border-top:1px solid #e2e8f0;
            padding-top:8px;
            color:#64748b;
        }
    </style>
</head>
<body>

@php
    $client = $devis->client;

    // Display client name
    $displayName = $client
        ? trim(($client->prenom ? $client->prenom.' ' : '').($client->nom_assure ?? ''))
        : ($devis->prospect_name ?? '—');

    // Helpers
    $fmtDate = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : null;
    $fmtKm = fn($v) => filled($v) ? number_format((int)$v, 0, ',', ' ').' km' : null;
@endphp

<!-- HEADER -->
<div class="top-line">
    <div class="company-info">
        @if($company)
            <strong>{{ $company->commercial_name ?? $company->name }}</strong><br>
            {{ $company->address }}<br>
            {{ $company->postal_code }} {{ $company->city }}<br>
            {{ $company->email }}<br>
            {{ $company->phone }}
        @endif
    </div>

    <div class="devis-info">
        <h2>DEVIS</h2>
        <p>#{{ $devis->numero ?? $devis->id }}</p>
        <p>Le {{ \Carbon\Carbon::parse($devis->date_devis)->format('d/m/Y') }}</p>
    </div>
</div>

<!-- CLIENT -->
<div class="client-info">
    <strong>{{ $displayName }}</strong><br>

    @if($client)
        @if($client->adresse) {{ $client->adresse }}<br> @endif
        @if(($client->code_postal ?? '') || ($client->ville ?? ''))
            {{ $client->code_postal }} {{ $client->ville }}<br>
        @endif
        @if($client->email) Email : {{ $client->email }}<br> @endif
        @if($client->telephone) Tél. : {{ $client->telephone }}<br> @endif
    @else
        @if($devis->prospect_email) {{ $devis->prospect_email }}<br> @endif
        @if($devis->prospect_phone) {{ $devis->prospect_phone }}<br> @endif
    @endif
</div>

<!-- VEHICLE SECTION -->
@if($client)
    <div class="section-title">Véhicule / Sinistre / Assurance</div>

    @php
        $lines = [];

        if($client->plaque) $lines[] = "Plaque : ".$client->plaque;
        if($client->kilometrage) $lines[] = "Kilométrage : ".$fmtKm($client->kilometrage);
        if($client->ancien_modele_plaque) $lines[] = "Ancien modèle : Oui";
        if($client->professionnel) $lines[] = "Professionnel : ".$client->professionnel;

        $l2 = [];
        if($client->type_vitrage) $l2[] = "Vitrage : ".$client->type_vitrage;
        if(!is_null($client->reparation))
            $l2[] = "Réparation : ".($client->reparation ? "Oui" : "Non");
        if($client->numero_police) $l2[] = "Police : ".$client->numero_police;
        if($client->numero_sinistre) $l2[] = "N° sinistre : ".$client->numero_sinistre;

        $l3 = [];
        if($client->nom_assurance) $l3[] = "Assurance : ".$client->nom_assurance;
        if($client->autre_assurance) $l3[] = "Autre : ".$client->autre_assurance;
        if($client->date_sinistre) $l3[] = "Date sinistre : ".$fmtDate($client->date_sinistre);
        if($client->date_declaration) $l3[] = "Déclaration : ".$fmtDate($client->date_declaration);
    @endphp

    @if(count($lines))
        <div class="vs-line">{{ implode(" / ", $lines) }}</div>
    @endif
    @if(count($l2))
        <div class="vs-line">{{ implode(" / ", $l2) }}</div>
    @endif
    @if(count($l3))
        <div class="vs-line">{{ implode(" / ", $l3) }}</div>
    @endif

    @if($client->adresse_pose)
        <div class="vs-line">
            Adresse de pose : {{ $client->adresse_pose }}
        </div>
    @endif
@endif

<!-- ITEMS -->
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

<!-- TOTALS -->
<table class="totals">
    <tr><td>Total HT</td><td style="text-align:right;">{{ number_format($devis->total_ht, 2, ',', ' ') }} €</td></tr>
    <tr><td>TVA (20%)</td><td style="text-align:right;">{{ number_format($devis->total_tva, 2, ',', ' ') }} €</td></tr>
    <tr><td><strong>Total TTC</strong></td>
        <td style="text-align:right;"><strong>{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</strong></td></tr>
</table>

<!-- VALIDITY + SIGNATURE INLINE -->
<div class="bottom-line">

    <!-- LEFT : modalité -->
    <div class="modalites">
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
    </div>

    <!-- RIGHT : signature -->
    <div class="signature">
        <div class="section-title">Signature</div>
        <div class="box">Bon pour accord<br>{{ $company->commercial_name ?? $company->name }}</div>
    </div>

</div>

<!-- FOOTER -->
<div class="footer">
    {{ $company->commercial_name ?? $company->name }}
    @if($company?->siret) — SIRET : {{ $company->siret }} @endif
    @if($company?->tva) — TVA : {{ $company->tva }} @endif
    @if($company?->ape) — APE : {{ $company->ape }} @endif
    @if($company?->rcs_number) — RCS : {{ $company->rcs_number }} {{ $company->rcs_city }} @endif
</div>

</body>
</html>