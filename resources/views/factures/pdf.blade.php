<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1f2937; margin: 0; padding: 24px 36px; background: #fff; }
        .header { display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 18px; }
        .company-info { font-size: 12px; line-height: 1.5; }
        .facture-info { text-align: right; }
        .facture-info h2 { margin: 0; font-size: 20px; color: #0ea5e9; letter-spacing: .5px; }
        .client-info { margin: 16px 0 20px; font-size: 12px; line-height: 1.55; }
        .section-title { font-weight: 700; margin: 18px 0 8px; color: #0f172a; text-transform: uppercase; font-size: 13px; }
        .kv { width: 100%; border-collapse: collapse; font-size: 12px; }
        .kv th, .kv td { border: 1px solid #e5e7eb; padding: 8px 10px; vertical-align: top; }
        .kv th { width: 30%; background: #f8fafc; color: #0f172a; text-align: left; font-weight: 600; }
        .grid2 { width: 100%; border-collapse: collapse; }
        .grid2 td { width: 50%; vertical-align: top; padding: 0 0 8px 0; }
        table.items { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 14px; }
        table.items th, table.items td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        table.items th { background: #e0f2fe; font-weight: 700; color: #0c4a6e; }
        .totals { width: 50%; margin-left: auto; margin-top: 16px; border-collapse: collapse; }
        .totals td { padding: 6px 8px; }
        .terms-box { border: 1px solid #e2e8f0; background: #f8fafc; padding: 12px 14px; border-radius: 6px; line-height: 1.55; white-space: pre-wrap; }
        .footer { font-size: 11px; margin-top: 28px; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; color: #64748b; }
        .muted { color:#6b7280 }
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

    $fmtDate = function ($v) {
        try { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : null; }
        catch (\Throwable $e) { return $v; }
    };
    $fmtKm = function ($v) {
        if ($v === null || $v === '') return null;
        $n = (int) $v;
        return number_format($n, 0, ',', ' ').' km';
    };

    // Payment terms rendering
    $companyName = $company->commercial_name ?? $company->name ?? 'Votre société';
    $method      = $facture->payment_method ?: 'Virement bancaire';
    $iban        = $facture->payment_iban ?: ($company->iban ?? '');
    $bic         = $facture->payment_bic  ?: ($company->bic  ?? '');
    $penalty     = $facture->penalty_rate;
    $dueDate     = $facture->due_date ? \Carbon\Carbon::parse($facture->due_date)->format('d/m/Y') : null;

    $termsText = trim((string) $facture->payment_terms_text);
    if ($termsText === '') {
        $lines = [];
        $lines[] = "Par {$method} à l'ordre de {$companyName}";
        if ($bic)  { $lines[] = "Code B.I.C : {$bic}"; }
        if ($iban) { $lines[] = "Code I.B.A.N : {$iban}"; }
        if ($dueDate) { $lines[] = "La présente facture sera payable au plus tard le : {$dueDate}"; }
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

{{-- CLIENT --}}
<div class="client-info">
    <strong>{{ $displayName }}</strong><br>
    @if($client)
        @if($addr1) {{ $addr1 }}<br>@endif
        @if($addr2) {{ $addr2 }}<br>@endif
        @if($client->email) {{ $client->email }}<br>@endif
        @if($client->telephone) {{ $client->telephone }}<br>@endif
    @else
        @if($prospectEmail) Email : {{ $prospectEmail }}<br>@endif
        @if($prospectPhone) Tél. : {{ $prospectPhone }}@endif
    @endif
</div>

{{-- VÉHICULE + SINISTRE + ASSURANCE (champs existants) --}}
@if($client)
    <div class="section-title">Véhicule / Sinistre / Assurance</div>
    <table class="kv">
        <tr>
            <th>Plaque d'immatriculation</th>
            <td>{{ $client->plaque ?: '—' }}</td>
            <th>Kilométrage</th>
            <td>{{ $fmtKm($client->kilometrage) ?: '—' }}</td>
        </tr>
        <tr>
            <th>Ancien modèle de plaque</th>
            <td>{{ $client->ancien_modele_plaque ? 'Oui' : 'Non' }}</td>
            <th>Professionnel</th>
            <td>{{ $client->professionnel ?: '—' }}</td>
        </tr>
        <tr>
            <th>Type de vitrage</th>
            <td>{{ $client->type_vitrage ?: '—' }}</td>
            <th>Réparation</th>
            <td>
                @if(!is_null($client->reparation))
                    {{ (string)$client->reparation === '1' ? 'Oui' : 'Non' }}
                @else
                    —
                @endif
            </td>
        </tr>
        <tr>
            <th>Raison du sinistre</th>
            <td>{{ $client->raison ?: '—' }}</td>
            <th>Numéro de police</th>
            <td>{{ $client->numero_police ?: '—' }}</td>
        </tr>
        <tr>
            <th>Date du sinistre</th>
            <td>{{ $fmtDate($client->date_sinistre) ?: '—' }}</td>
            <th>Date de déclaration</th>
            <td>{{ $fmtDate($client->date_declaration) ?: '—' }}</td>
        </tr>
        <tr>
            <th>Assurance</th>
            <td>{{ $client->nom_assurance ?: '—' }}</td>
            <th>Autre assurance</th>
            <td>{{ $client->autre_assurance ?: '—' }}</td>
        </tr>
        <tr>
            <th>N° de sinistre</th>
            <td>{{ $client->numero_sinistre ?: '—' }}</td>
            <th>Adresse de pose</th>
            <td>{{ $client->adresse_pose ?: '—' }}</td>
        </tr>
    </table>
@endif

{{-- PRESTATIONS --}}
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
    @foreach($facture->items as $item)
        <tr>
            <td>
                <div>{{ $item->produit }}</div>
                @if($item->description)
                    <div class="muted">{{ $item->description }}</div>
                @endif
            </td>
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

<div class="footer">
    {{ $companyName }}
    @if($company?->siret) — SIRET: {{ $company->siret }} @endif
    @if($company?->tva) — TVA: {{ $company->tva }} @endif
    @if($company?->ape) — Code APE: {{ $company->ape }} @endif
    @if($company?->rcs_number) — RCS: {{ $company->rcs_number }} {{ $company->rcs_city }} @endif
</div>

</body>
</html>