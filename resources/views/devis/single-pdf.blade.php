<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis #{{ $devis->numero ?? $devis->id }}</title>
    <style>
        body { font-family:'Segoe UI',sans-serif; font-size:12px; color:#1f2937; margin:0; padding:20px 40px; background:#fff; }
        .header { display:flex; justify-content:space-between; border-bottom:1px solid #e2e8f0; margin-bottom:20px; }
        .company-info { font-size:12px; line-height:1.5; }
        .devis-info { text-align:right; }
        .devis-info h2 { margin:0; font-size:20px; color:#0ea5e9; }
        .client-info { margin-bottom:20px; font-size:12px; line-height:1.5; }
        .section-title { font-weight:bold; margin:20px 0 10px; text-transform:uppercase; color:#0f172a; }
        table { width:100%; border-collapse:collapse; font-size:12px; margin-bottom:20px; }
        table th, table td { border-bottom:1px solid #ccc; padding:8px; text-align:left; }
        table th { background:#e0f2fe; font-weight:bold; color:#0c4a6e; }
        .totals { width:50%; margin-left:auto; margin-top:20px; }
        .totals td { padding:6px 8px; }
        .footer { font-size:11px; margin-top:40px; text-align:center; border-top:1px solid #e2e8f0; padding-top:10px; color:#64748b; }
        .signature { margin-top:40px; }
        .signature div { width:45%; display:inline-block; text-align:center; margin-top:40px; border-top:1px dashed #94a3b8; padding-top:10px; }
        .bank-info { font-size:11px; margin-top:10px; line-height:1.6; }
        .muted { color:#64748b; }
    </style>
</head>
<body>

<div class="header">
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
        <p>{{ $company->city ?? '' }}, le {{ \Carbon\Carbon::parse($devis->date_devis)->format('d/m/Y') }}</p>
    </div>
</div>

<div class="client-info">
    @if($devis->client)
        <strong>{{ $devis->client->prenom }} {{ $devis->client->nom_assure }}</strong><br>
        {{ $devis->client->adresse }}<br>
        {{ $devis->client->code_postal }} {{ $devis->client->ville }}<br>

        @php
            $plaque = trim((string)($devis->client->plaque ?? ''));
            $kmVal  = $devis->client->kilometrage ?? null;
            $kmAff  = is_null($kmVal) || $kmVal === '' ? null
                    : (is_numeric($kmVal) ? number_format((float)$kmVal, 0, ',', ' ') : (string)$kmVal);
        @endphp

        @if($plaque)
            <span><strong>Immatriculation :</strong> {{ $plaque }}</span><br>
        @endif

        @if($kmAff)
            <span><strong>Kilométrage :</strong> {{ $kmAff }} km</span>
        @endif
    @else
        @if(!empty($devis->prospect_name))
            <strong>{{ $devis->prospect_name }}</strong><br>
            @if(!empty($devis->prospect_email)) {{ $devis->prospect_email }}<br>@endif
            @if(!empty($devis->prospect_phone)) {{ $devis->prospect_phone }}<br>@endif
        @endif
    @endif
</div>

<div class="section-title">Détails des prestations</div>
<table>
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
                <td>{{ $item->produit }}</td>
                <td>{{ number_format($item->prix_unitaire, 2, ',', ' ') }} €</td>
                <td>{{ rtrim(rtrim(number_format((float)$item->quantite, 2, ',', ' '), '0'), ',') }}</td>
                <td>{{ number_format($item->total_ht, 2, ',', ' ') }} €</td>
            </tr>
        @endforeach
    </tbody>
</table>

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

{{-- MODALITÉS DE PAIEMENT --}}
<div class="section-title">Modalités de paiement</div>
<div class="bank-info">
    <p>
        Par virement bancaire ou chèque à l’ordre de
        <strong>{{ $company->commercial_name ?? $company->name }}</strong>.
    </p>
    <p>
        IBAN : {{ $company->iban ?: '—' }}<br>
        BIC&nbsp;&nbsp;&nbsp;: {{ $company->bic  ?: '—' }}
    </p>
    <p class="muted">
        Ce devis est valable jusqu’au
        <strong>{{ \Carbon\Carbon::parse($devis->date_validite)->format('d/m/Y') }}</strong>.
    </p>
</div>

<div class="signature">
    <div>Bon pour accord</div>
    <div>{{ $company->commercial_name ?? $company->name }}</div>
</div>

<div class="footer">
    {{ $company->commercial_name ?? $company->name }} — SIRET: {{ $company->siret }} — TVA: {{ $company->tva }}<br>
    Code APE: {{ $company->ape }} — RCS: {{ $company->rcs_number }} {{ $company->rcs_city }}
</div>

</body>
</html>