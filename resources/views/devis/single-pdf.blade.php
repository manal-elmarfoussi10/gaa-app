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

        /* TABLE DESIGN */
        table.items { width:100%; border-collapse:collapse; font-size:12px; margin-top:14px; }
        table.items th, table.items td { border-bottom:1px solid #e5e7eb; padding:8px; text-align:left; }
        table.items th { background:#e0f2fe; font-weight:700; color:#0c4a6e; }

        .totals { width:50%; margin-left:auto; margin-top:16px; border-collapse:collapse; }
        .totals td { padding:6px 8px; }

        /* NEW COMPACT STYLE */
        .compact-block {
            font-size:12px;
            line-height:1.45;
            margin-bottom:14px;
        }
        .compact-block p { margin:3px 0; }

        .line-list span {
            margin-right:10px;
        }

        /* PAYMENT BLOCK */
        .payment-block { 
            font-size:12px; 
            line-height:1.45; 
            margin-top:16px;
        }

        .signature { margin-top:40px; display:flex; justify-content:space-between; }
        .signature div { width:45%; text-align:center; border-top:1px dashed #94a3b8; padding-top:10px; font-size:12px; }

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
    $fmtKm = fn($v) => filled($v) ? number_format((int)$v, 0, ',', ' ').' km' : '—';
@endphp

<!-- HEADER -->
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

<!-- CLIENT INFO -->
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


<!-- VEHICULE COMPACT -->
@if($client)
<div class="section-title">VÉHICULE / SINISTRE / ASSURANCE</div>

<div class="compact-block">

    <p class="line-list">
        <span><strong>Plaque :</strong> {{ $client->plaque ?: '—' }}</span> /
        <span><strong>Kilométrage :</strong> {{ $fmtKm($client->kilometrage) }}</span> /
        <span><strong>Ancien modèle :</strong> {{ $client->ancien_modele_plaque ? 'Oui' : 'Non' }}</span> /
        <span><strong>Professionnel :</strong> {{ $client->professionnel ?: '—' }}</span>
    </p>

    <p class="line-list">
        <span><strong>Vitrage :</strong> {{ $client->type_vitrage ?: '—' }}</span> /
        <span><strong>Réparation :</strong> {{ $client->reparation ? 'Oui' : 'Non' }}</span> /
        <span><strong>Police :</strong> {{ $client->numero_police ?: '—' }}</span> /
        <span><strong>N° sinistre :</strong> {{ $client->numero_sinistre ?: '—' }}</span>
    </p>

    <p class="line-list">
        <span><strong>Assurance :</strong> {{ $client->nom_assurance ?: '—' }}</span> /
        <span><strong>Autre :</strong> {{ $client->autre_assurance ?: '—' }}</span> /
        <span><strong>Date sinistre :</strong> {{ $fmtDate($client->date_sinistre) ?: '—' }}</span> /
        <span><strong>Déclaration :</strong> {{ $fmtDate($client->date_declaration) ?: '—' }}</span>
    </p>

    <p><strong>Adresse de pose :</strong> {{ $client->adresse_pose ?: '—' }}</p>

</div>
@endif


<!-- PRESTATIONS -->
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
    <tr><td>Total HT</td><td style="text-align:right;">{{ number_format($devis->total_ht, 2, ',', ' ') }} €</td></tr>
    <tr><td>TVA (20%)</td><td style="text-align:right;">{{ number_format($devis->total_tva, 2, ',', ' ') }} €</td></tr>
    <tr><td><strong>Total TTC</strong></td><td style="text-align:right;"><strong>{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</strong></td></tr>
</table>


<!-- MODALITÉS DE PAIEMENT (COMPACTE) -->
<div class="section-title">Modalités & conditions de règlement</div>

<div class="payment-block">
    <p><strong>Règlement :</strong> Virement bancaire ou chèque à l'ordre de {{ $company->commercial_name ?? $company->name }}.</p>

    <p><strong>BIC :</strong> {{ $company->bic ?? '—' }}</p>
    <p><strong>IBAN :</strong> {{ $company->iban ?? '—' }}</p>

    <p style="margin-top:6px;">
        La présente facture sera payable au plus tard le : <strong>{{ $fmtDate($devis->date_validite) }}</strong>.
    </p>

    <p class="muted" style="margin-top:4px;">
        Passé ce délai, une pénalité pourra être appliquée selon le Code du commerce.  
        Une indemnité forfaitaire de 40 € peut être exigible.
    </p>
</div>


<!-- SIGNATURE -->
<div class="signature">
    <div>Bon pour accord</div>
    <div>{{ $company->commercial_name ?? $company->name }}</div>
</div>


<!-- FOOTER -->
<div class="footer">
    {{ $company->commercial_name ?? $company->name }}
    @if($company?->siret) — SIRET: {{ $company->siret }} @endif
    @if($company?->tva) — TVA: {{ $company->tva }} @endif
    @if($company?->ape) — Code APE: {{ $company->ape }} @endif
    @if($company?->rcs_number) — RCS: {{ $company->rcs_number }} {{ $company->rcs_city }} @endif
</div>

</body>
</html>