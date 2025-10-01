<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; font-size: 12px; color: #1f2937; margin: 0; padding: 20px 40px; background: #fff; }
        .header { display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px; }
        .company-info { font-size: 12px; line-height: 1.5; }
        .facture-info { text-align: right; }
        .facture-info h2 { margin: 0; font-size: 20px; color: #0ea5e9; }
        .client-info { margin-bottom: 20px; font-size: 12px; line-height: 1.5; }
        .prestations-title { font-weight: bold; margin: 20px 0 10px; text-transform: uppercase; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
        table th, table td { border-bottom: 1px solid #ccc; padding: 8px; text-align: left; }
        table th { background: #e0f2fe; font-weight: bold; color: #0c4a6e; }
        .totals { width: 50%; margin-left: auto; margin-top: 20px; }
        .totals td { padding: 6px 8px; }
        .section-title { font-weight: 600; margin: 24px 0 8px; color: #0f172a; }
        .terms-box { border: 1px solid #e2e8f0; background: #f8fafc; padding: 12px 14px; border-radius: 6px; line-height: 1.55; white-space: pre-wrap; }
        .footer { font-size: 11px; margin-top: 40px; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; color: #64748b; }
    </style>
</head>
<body>
@php
    $client        = $facture->client; // can be null
    $prospectName  = $facture->prospect_name  ?? optional($facture->devis)->prospect_name;
    $prospectEmail = $facture->prospect_email ?? optional($facture->devis)->prospect_email;
    $prospectPhone = $facture->prospect_phone ?? optional($facture->devis)->prospect_phone;

    $displayName = $client
        ? trim(($client->prenom ? $client->prenom.' ' : '').($client->nom_assure ?? ''))
        : ($prospectName ?? '—');

    $addr1 = $client ? ($client->adresse ?? '') : null;
    $addr2 = $client ? trim(($client->code_postal ?? '').' '.($client->ville ?? '')) : null;

    $km = $client && filled($client->kilometrage) ? (int) $client->kilometrage : null;

    // Payment terms rendering
    $companyName = $company->commercial_name ?? $company->name ?? 'Votre société';
    $method      = $facture->payment_method ?: 'Virement bancaire';
    $iban        = $facture->payment_iban ?: ($company->iban ?? '');
    $bic         = $facture->payment_bic  ?: ($company->bic  ?? '');
    $penalty     = $facture->penalty_rate;
    $dueDate     = $facture->due_date ? \Carbon\Carbon::parse($facture->due_date)->format('d/m/Y') : null;

    // Prefer the custom text if present; otherwise compose a nice default.
    $termsText = trim((string) $facture->payment_terms_text);
    if ($termsText === '') {
        $lines = [];
        $lines[] = "Par {$method} à l'ordre de {$companyName}";
        if ($bic)  { $lines[] = "Code B.I.C : {$bic}"; }
        if ($iban) { $lines[] = "Code I.B.A.N : {$iban}"; }
        if ($dueDate) {
            $lines[] = "La présente facture sera payable au plus tard le : {$dueDate}";
        }
        $lines[] = "Passé ce délai, sans obligation d’envoi d’une relance, une pénalité sera appliquée conformément au Code de commerce."
                 . ($penalty !== null && $penalty !== '' ? " Taux des pénalités de retard : {$penalty}%." : "");
        $lines[] = "Une indemnité forfaitaire pour frais de recouvrement de 40€ est également exigible.";
        $termsText = implode("\n", $lines);
    }
@endphp

<div class="header">
    <div class="company-info">
        @if($company)
            <strong>{{ $companyName }}</strong><br>
            {{ $company->address }}<br>
            {{ $company->postal_code }} {{ $company->city }}<br>
            {{ $company->email }}<br>
            {{ $company->phone }}
        @endif
    </div>
    <div class="facture-info">
        <h2>FACTURE</h2>
        <p>N° {{ $facture->numero }}</p>
        <p>{{ $company->city ?? '' }}, le {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</p>
    </div>
</div>

<div class="client-info">
    <strong>{{ $displayName }}</strong><br>

    @if($client)
        @if($addr1) {{ $addr1 }}<br>@endif
        @if($addr2) {{ $addr2 }}<br>@endif
        @if($km !== null) Kilométrage : {{ number_format($km, 0, ',', ' ') }} km @endif
    @else
        @if($prospectEmail) Email : {{ $prospectEmail }}<br>@endif
        @if($prospectPhone) Tél. : {{ $prospectPhone }}@endif
    @endif
</div>

<div class="prestations-title">Détails des prestations</div>
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
    @foreach($facture->items as $item)
        <tr>
            <td>{{ $item->produit }}</td>
            <td>{{ number_format((float)$item->prix_unitaire, 2, ',', ' ') }} €</td>
            <td>{{ rtrim(rtrim(number_format((float)$item->quantite, 2, ',', ' '), '0'), ',') }}</td>
            <td>{{ number_format((float)$item->total_ht, 2, ',', ' ') }} €</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Total HT</td>
        <td style="text-align:right;">{{ number_format((float)$facture->total_ht, 2, ',', ' ') }} €</td>
    </tr>
    <tr>
        <td>TVA</td>
        <td style="text-align:right;">{{ number_format((float)$facture->total_tva, 2, ',', ' ') }} €</td>
    </tr>
    <tr>
        <td><strong>Total TTC</strong></td>
        <td style="text-align:right;"><strong>{{ number_format((float)$facture->total_ttc, 2, ',', ' ') }} €</strong></td>
    </tr>
</table>

{{-- Modalités & conditions de règlement --}}
<div class="section-title">Modalités & conditions de règlement</div>
<div class="terms-box">{!! nl2br(e($termsText)) !!}</div>

{{-- (Removed the “Bon pour accord” block per your request) --}}

<div class="footer">
    {{ $companyName }}
    @if($company?->siret) — SIRET: {{ $company->siret }} @endif
    @if($company?->tva) — TVA: {{ $company->tva }} @endif
    @if($company?->ape) — Code APE: {{ $company->ape }} @endif
    @if($company?->rcs_number) — RCS: {{ $company->rcs_number }} {{ $company->rcs_city }} @endif
</div>

</body>
</html>