{{-- resources/views/avoirs/single_pdf.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avoir #{{ $avoir->id }} — Facture {{ $avoir->facture->numero ?? ('#'.$avoir->facture->id) }}</title>
    <style>
        body { font-family:'Segoe UI',sans-serif; font-size:12px; color:#1f2937; margin:0; padding:20px 40px; background:#fff; }
        .header { display:flex; justify-content:space-between; border-bottom:1px solid #e2e8f0; margin-bottom:20px; }
        .company-info { font-size:12px; line-height:1.5; }
        .doc-info { text-align:right; }
        .doc-info h2 { margin:0; font-size:20px; color:#0ea5e9; }
        .client-info { margin-bottom:20px; font-size:12px; line-height:1.5; }
        .section-title { font-weight:600; margin:20px 0 10px; text-transform:uppercase; color:#0f172a; }
        table { width:100%; border-collapse:collapse; font-size:12px; margin-bottom:20px; }
        th, td { border-bottom:1px solid #e5e7eb; padding:8px; text-align:left; }
        th { background:#e0f2fe; font-weight:700; color:#0c4a6e; }
        .totals { width:50%; margin-left:auto; margin-top:20px; }
        .totals td { padding:6px 8px; }
        .bank-info { font-size:11px; margin-top:10px; line-height:1.6; }
        .signature { margin-top:40px; }
        .signature div { width:45%; display:inline-block; text-align:center; margin-top:40px; border-top:1px dashed #94a3b8; padding-top:10px; }
        .footer { font-size:11px; margin-top:40px; text-align:center; border-top:1px solid #e2e8f0; padding-top:10px; color:#64748b; }
        .badge { display:inline-block; padding:4px 8px; border-radius:6px; background:#f1f5f9; font-size:11px; color:#334155; }
    </style>
</head>
@php
    $facture = $avoir->facture; // related invoice
    // Prefer client name; otherwise show prospect snapshot from facture (if any)
    $displayName = optional($facture->client)->prenom
        ? trim($facture->client->prenom.' '.($facture->client->nom_assure ?? ''))
        : ($facture->prospect_name ?? '-');

    $addr1 = optional($facture->client)->adresse;
    $addr2 = trim((optional($facture->client)->code_postal ?? '').' '.(optional($facture->client)->ville ?? ''));

    // Company shortcuts (null-safe)
    $c = $company ?? null;
@endphp
<body>

<div class="header">
    <div class="company-info">
        @if($c)
            <strong>{{ $c->commercial_name ?? $c->name ?? 'Votre société' }}</strong><br>
            {{ $c->address ?? '' }}<br>
            {{ $c->postal_code ?? '' }} {{ $c->city ?? '' }}<br>
            {{ $c->email ?? '' }}<br>
            {{ $c->phone ?? '' }}
        @endif
    </div>
    <div class="doc-info">
        <h2>AVOIR</h2>
        <p>N° {{ $avoir->id }}</p>
        <p>
            Facture liée :
            <span class="badge">{{ $facture->numero ?? ('#'.$facture->id) }}</span>
        </p>
        <p>{{ $c->city ?? '' }}, le {{ optional($avoir->created_at)->format('d/m/Y') }}</p>
    </div>
</div>

<div class="client-info">
    <strong>{{ $displayName }}</strong><br>
    @if($facture->client)
        @if(!empty($addr1)) {{ $addr1 }}<br>@endif
        @if(trim($addr2) !== '') {{ $addr2 }}<br>@endif
        @php
            $km = $facture->client->kilometrage ?? null;
            $kmAff = is_null($km) || $km === '' ? null
                    : (is_numeric($km) ? number_format((float)$km, 0, ',', ' ') : (string)$km);
        @endphp
        @if($kmAff) Kilométrage : {{ $kmAff }} km @endif
    @else
        @if(!empty($facture->prospect_address)) {{ $facture->prospect_address }}<br>@endif
        @if(!empty($facture->prospect_email)) Email : {{ $facture->prospect_email }}<br>@endif
        @if(!empty($facture->prospect_phone)) Tél. : {{ $facture->prospect_phone }}@endif
    @endif
</div>

<div class="section-title">Détails de l’avoir</div>
<table>
    <thead>
    <tr>
        <th>Description</th>
        <th>Montant</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            Avoir émis sur la facture
            <strong>{{ $facture->numero ?? ('#'.$facture->id) }}</strong>
            @if(!empty($avoir->notes))
                <br><em>{{ $avoir->notes }}</em>
            @endif
        </td>
        <td>{{ number_format((float)$avoir->montant, 2, ',', ' ') }} €</td>
    </tr>
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Total Avoir</td>
        <td style="text-align:right;"><strong>{{ number_format((float)$avoir->montant, 2, ',', ' ') }} €</strong></td>
    </tr>
</table>

<div class="bank-info">
    <p><strong>Modalités de règlement</strong> : Par virement bancaire (remboursement) ou chèque à l’ordre de
        {{ $c->commercial_name ?? $c->name ?? 'Votre société' }}.</p>
    <p>
        IBAN : {{ $c->iban ?? '…' }}<br>
        BIC&nbsp;&nbsp;&nbsp;: {{ $c->bic  ?? '…' }}
    </p>
    <p style="margin-top:6px;">
        Cet avoir vient en déduction du solde de la facture liée. En cas de trop-perçu, un remboursement pourra être
        opéré selon les modalités ci-dessus.
    </p>
</div>

<div class="signature">
    <div>Bon pour accord</div>
    <div>{{ $c->commercial_name ?? $c->name ?? 'Votre société' }}</div>
</div>

<div class="footer">
    {{ $c->commercial_name ?? $c->name ?? 'Votre société' }}
    @if(!empty($c?->siret)) — SIRET: {{ $c->siret }} @endif
    @if(!empty($c?->tva)) — TVA: {{ $c->tva }} @endif
    <br>
    @if(!empty($c?->ape)) Code APE: {{ $c->ape }} @endif
    @if(!empty($c?->rcs_number)) — RCS: {{ $c->rcs_number }} {{ $c->rcs_city ?? '' }} @endif
</div>

</body>
</html>