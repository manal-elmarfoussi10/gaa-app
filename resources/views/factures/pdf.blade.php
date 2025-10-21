<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        @page { margin: 24px 28px; }
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Arial, sans-serif; font-size: 12px; color: #111827; margin:0; }
        .grid { display: grid; gap: 16px; }
        .header { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1px solid #e5e7eb; padding-bottom:12px; }
        .brand { display:flex; gap:12px; align-items:flex-start; }
        .brand img { height:54px; object-fit:contain; }
        .company-info { line-height:1.5; }
        .company-info .name { font-weight:700; font-size:14px; }
        .docbox { text-align:right; }
        .docbox h1 { margin:0; font-size:22px; color:#0ea5e9; letter-spacing:.3px; }
        .docbox p { margin:2px 0; }
        .meta { grid-template-columns: 1fr 1fr; margin-top:16px; }
        .card { border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#fff; }
        .title-xs { font-size:11px; text-transform:uppercase; color:#6b7280; letter-spacing:.04em; margin-bottom:6px; }
        .subtitle { color:#374151; font-weight:600; }
        .muted { color:#6b7280; }

        table { width:100%; border-collapse:collapse; }
        th, td { padding:8px 10px; vertical-align:top; }
        thead th { background:#e0f2fe; color:#0c4a6e; font-weight:700; border-bottom:1px solid #dbeafe; }
        tbody td { border-bottom:1px solid #f3f4f6; }
        .right { text-align:right; }
        .totals { width:50%; margin-left:auto; margin-top:14px; }
        .totals td { padding:6px 8px; }
        .terms { border:1px solid #e5e7eb; background:#f9fafb; border-radius:8px; padding:12px; white-space:pre-wrap; line-height:1.55; }

        /* keep tables together on one page */
        table, .card, .terms { page-break-inside: avoid; }
        h1, h2, h3, .header, .footer { page-break-after: avoid; }

        .footer { margin-top:18px; padding-top:10px; border-top:1px solid #e5e7eb; text-align:center; color:#6b7280; font-size:11px; }
    </style>
</head>
<body>
@php
    // ------ Helpers ------
    $fmt = fn($n) => number_format((float)$n, 2, ',', ' ');
    $fmtInt = fn($n) => rtrim(rtrim(number_format((float)$n, 2, ',', ' '), '0'), ',');
    $fmtDate = function ($v) {
        try { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : null; }
        catch (\Throwable $e) { return $v; }
    };

    // ------ Company (seller) ------
    /** @var \App\Models\Company|null $company */
    $companyName  = $company->commercial_name ?: ($company->name ?? 'Votre société');
    $sellerCity   = $company->city ?? '';
    $sellerAddr   = trim(($company->address ?? '').($company->postal_code || $company->city ? "\n" : ''))
                  . trim(($company->postal_code ?? '').' '.($company->city ?? ''));
    $sellerLines  = array_filter([
        $companyName,
        $company->address ? $company->address : null,
        trim(($company->postal_code ?? '').' '.($company->city ?? '')) ?: null,
        $company->email ?: null,
        $company->phone ?: null,
    ]);

    // Legal/IDs to show compactly
    $legalBits = array_filter([
        $company?->legal_form ? 'Forme : '.$company->legal_form : null,
        $company?->capital    ? ('Capital : '.(is_numeric($company->capital) ? $fmtInt($company->capital).' €' : $company->capital)) : null,
        $company?->siret      ? 'SIRET : '.$company->siret : null,
        $company?->tva        ? 'TVA : '.$company->tva : null,
        $company?->naf_code   ? 'NAF/APE : '.$company->naf_code : null,
        $company?->rcs_number ? ('RCS : '.$company->rcs_number.($company?->rcs_city ? ' '.$company->rcs_city : '')) : null,
    ]);

    // Payment terms defaults (can be overridden per-facture)
    $method   = $facture->payment_method ?: ($company->methode_paiement ?: 'Virement bancaire');
    $iban     = $facture->payment_iban ?: ($company->iban ?? '');
    $bic      = $facture->payment_bic  ?: ($company->bic  ?? '');
    $penalty  = $facture->penalty_rate ?? $company->penalty_rate;
    $dueDate  = $facture->due_date ? $fmtDate($facture->due_date) : null;

    $termsText = trim((string) $facture->payment_terms_text);
    if ($termsText === '') {
        $lines = [];
        $lines[] = "Par {$method} à l'ordre de {$companyName}";
        if ($bic)  $lines[] = "Code B.I.C : {$bic}";
        if ($iban) $lines[] = "Code I.B.A.N : {$iban}";
        if ($dueDate) $lines[] = "La présente facture sera payable au plus tard le : {$dueDate}";
        $lines[] = "Passé ce délai, sans obligation d’envoi d’une relance, une pénalité sera appliquée conformément au Code de commerce."
                 . ($penalty !== null && $penalty !== '' ? " Taux des pénalités de retard : {$penalty}%." : "");
        $lines[] = "Une indemnité forfaitaire pour frais de recouvrement de 40€ est également exigible.";
        $termsText = implode("\n", $lines);
    }

    // ------ Client / prospect ------
    $client        = $facture->client; // peut être null
    $prospectName  = $facture->prospect_name  ?? optional($facture->devis)->prospect_name;
    $prospectEmail = $facture->prospect_email ?? optional($facture->devis)->prospect_email;
    $prospectPhone = $facture->prospect_phone ?? optional($facture->devis)->prospect_phone;

    $displayName = $client
        ? trim(($client->prenom ? $client->prenom.' ' : '').($client->nom_assure ?? ''))
        : ($prospectName ?: '—');

    $addr1 = $client ? ($client->adresse ?? '') : null;
    $addr2 = $client ? trim(($client->code_postal ?? '').' '.($client->ville ?? '')) : null;
@endphp

<!-- Header -->
<div class="header">
    <div class="brand">
        @if($company?->logo)
            <img src="{{ public_path('storage/'.$company->logo) }}" alt="Logo">
        @endif
        <div class="company-info">
            <div class="name">{{ $companyName }}</div>
            @foreach($sellerLines as $line)
                <div>{{ $line }}</div>
            @endforeach
        </div>
    </div>
    <div class="docbox">
        <h1>FACTURE</h1>
        <p><strong>N°</strong> {{ $facture->numero }}</p>
        <p>{{ $sellerCity ?: $companyName }}, le {{ $fmtDate($facture->date_facture) }}</p>
    </div>
</div>

<!-- Seller / Client blocks -->
<div class="grid meta">
    <div class="card">
        <div class="title-xs">Émetteur</div>
        <div class="subtitle">{{ $companyName }}</div>
        @if($company?->address)
            <div class="muted" style="white-space:pre-line;">{{ $sellerAddr }}</div>
        @endif
        <div class="muted" style="margin-top:6px;">
            @if($company?->siret) <div>SIRET : {{ $company->siret }}</div> @endif
            @if($company?->tva)   <div>TVA : {{ $company->tva }}</div> @endif
            @if($company?->naf_code) <div>NAF/APE : {{ $company->naf_code }}</div> @endif
            @if($company?->rcs_number) <div>RCS : {{ $company->rcs_number }} {{ $company->rcs_city }}</div> @endif
        </div>
    </div>
    <div class="card">
        <div class="title-xs">Client</div>
        <div class="subtitle">{{ $displayName }}</div>
        <div class="muted">
            @if($client)
                @if($addr1) <div>{{ $addr1 }}</div> @endif
                @if($addr2) <div>{{ $addr2 }}</div> @endif
                @if($client->email) <div>{{ $client->email }}</div> @endif
                @if($client->telephone) <div>{{ $client->telephone }}</div> @endif
            @else
                @if($prospectEmail) <div>Email : {{ $prospectEmail }}</div> @endif
                @if($prospectPhone) <div>Tél. : {{ $prospectPhone }}</div> @endif
            @endif
        </div>
    </div>
</div>

{{-- Vehicle / Sinistre / Assurance (if client exists) --}}
@if($client)
    <div class="card" style="margin-top:12px;">
        <div class="title-xs">Véhicule / Sinistre / Assurance</div>
        <table>
            <tbody>
            <tr>
                <th style="width:28%; text-align:left;">Plaque d'immatriculation</th>
                <td>{{ $client->plaque ?: '—' }}</td>
                <th style="width:22%; text-align:left;">Kilométrage</th>
                <td>{{ $client->kilometrage ? number_format((int)$client->kilometrage, 0, ',', ' ').' km' : '—' }}</td>
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
            </tbody>
        </table>
    </div>
@endif

<!-- Items -->
<div class="card" style="margin-top:12px;">
    <div class="title-xs">Détails des prestations</div>
    <table>
        <thead>
        <tr>
            <th>Description</th>
            <th class="right" style="width:18%;">Prix unitaire</th>
            <th class="right" style="width:10%;">Qté</th>
            <th class="right" style="width:18%;">Montant HT</th>
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
                <td class="right">{{ $fmt($item->prix_unitaire) }} €</td>
                <td class="right">{{ $fmtInt($item->quantite) }}</td>
                <td class="right">{{ $fmt($item->total_ht) }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Total HT</td>
            <td class="right">{{ $fmt($facture->total_ht) }} €</td>
        </tr>
        <tr>
            <td>TVA</td>
            <td class="right">{{ $fmt($facture->total_tva) }} €</td>
        </tr>
        <tr>
            <td><strong>Total TTC</strong></td>
            <td class="right"><strong>{{ $fmt($facture->total_ttc) }} €</strong></td>
        </tr>
    </table>
</div>

<!-- Payment terms -->
<div class="card" style="margin-top:12px;">
    <div class="title-xs">Modalités & conditions de règlement</div>
    <div class="terms">{!! nl2br(e($termsText)) !!}</div>
</div>

<!-- Footer -->
<div class="footer">
    {{ $companyName }}
    @if(!empty($legalBits))
        — {{ implode(' — ', $legalBits) }}
    @endif
</div>
</body>
</html>