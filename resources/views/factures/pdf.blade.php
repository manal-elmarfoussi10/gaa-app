<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        /* ---------- Print / page rules ---------- */
        @page   { size: A4; margin: 9mm 8mm; }  /* tighter margins to fit one page */
        html, body { height: 100%; background: #fff; }
        * { box-sizing: border-box; }

        /* ---------- Base ---------- */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
               color:#1f2937; font-size: 11px; line-height: 1.35; margin:0; }
        .wrap { padding: 0; }

        .row { display:flex; gap:10px; }
        .col { flex:1 1 0; }
        .right { text-align:right; }
        .muted { color:#6b7280; }

        h1,h2,h3,p,ul { margin:0; }
        h2.title { font-size: 18px; color:#0ea5e9; letter-spacing:.3px; }
        .chip { display:inline-block; background:#eef2f7; border:1px solid #e5e7eb; border-radius:6px; padding:6px 8px; }

        /* ---------- Sections ---------- */
        .section { margin-top:10px; }
        .section-title { font-weight:700; color:#0f172a; font-size: 11px; text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px; }

        /* ---------- Header ---------- */
        .header { display:flex; align-items:flex-start; justify-content:space-between;
                  border-bottom:1px solid #e5e7eb; padding-bottom:8px; }
        .company-block { display:flex; gap:10px; align-items:flex-start; }
        .logo { width:52px; height:52px; object-fit:contain; }
        .company-info { font-size: 10.5px; }
        .facture-info { text-align:right; }
        .fact-meta { margin-top:4px; font-size:10.5px; }

        /* ---------- Grids / Tables ---------- */
        table { width:100%; border-collapse:collapse; }
        .kv td, .kv th { border:1px solid #e5e7eb; padding:5px 6px; vertical-align:top; }
        .kv th { width:28%; background:#f8fafc; color:#0f172a; text-align:left; font-weight:600; }

        .items th, .items td { border-bottom:1px solid #e5e7eb; padding:6px; }
        .items thead th { background:#e0f2fe; color:#0c4a6e; font-weight:700; }

        .totals { width: 48%; margin-left:auto; }
        .totals td { padding:5px 6px; }
        .totals tr:last-child td { font-weight:700; }

        .terms { border:1px solid #e5e7eb; background:#f8fafc; border-radius:6px; padding:8px 10px; line-height:1.35; }

        .footer { text-align:center; color:#64748b; font-size:10px; border-top:1px solid #e5e7eb; padding-top:7px; margin-top:8px; }

        /* ---------- One-page helpers ---------- */
        .no-break { page-break-inside: avoid; }
        .tight p { margin:0; }
        .compact ul { padding-left:14px; margin:0; }
        .small { font-size:10px; }

        /* If content still risks overflow, slightly shrink everything for print */
        @media print {
            body { zoom: 0.96; } /* gentle global shrink to help keep to one page */
        }
    </style>
</head>
<body>
@php
    $client        = $facture->client;
    $prospectName  = $facture->prospect_name  ?? optional($facture->devis)->prospect_name;
    $prospectEmail = $facture->prospect_email ?? optional($facture->devis)->prospect_email;
    $prospectPhone = $facture->prospect_phone ?? optional($facture->devis)->prospect_phone;

    $displayName = $client
        ? trim(($client->prenom ? $client->prenom.' ' : '').($client->nom_assure ?? ''))
        : ($prospectName ?? '—');

    $addr1 = $client ? ($client->adresse ?? '') : null;
    $addr2 = $client ? trim(($client->code_postal ?? '').' '.($client->ville ?? '')) : null;

    $fmtDate = function ($v) { try { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : null; } catch (\Throwable $e) { return $v; } };
    $fmtKm   = function ($v) { if ($v === null || $v === '') return null; $n=(int)$v; return number_format($n,0,',',' ').' km'; };

    $companyName = $company->commercial_name ?? $company->name ?? 'Votre société';
    $method      = $facture->payment_method ?: ($company->methode_paiement ?: 'Virement bancaire');
    $iban        = $facture->payment_iban ?: ($company->iban ?? '');
    $bic         = $facture->payment_bic  ?: ($company->bic  ?? '');
    $penalty     = $facture->penalty_rate ?? $company->penalty_rate;
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

    $logoUrl = isset($company->logo) && $company->logo
        ? (Str::startsWith($company->logo, ['http://','https://']) ? $company->logo : (asset('storage/'.$company->logo)))
        : null;
@endphp

<div class="wrap">

    {{-- ============ HEADER ============ --}}
    <div class="header no-break">
        <div class="company-block">
            <div class="company-info">
                <strong style="font-size:12px">{{ $companyName }}</strong><br>
                @if($company?->address)
                    {{ $company->address }}<br>
                    {{ $company->postal_code }} {{ $company->city }}<br>
                @endif
                @if($company?->email) {{ $company->email }}<br>@endif
                @if($company?->phone) {{ $company->phone }}@endif
            </div>
        </div>

        <div class="facture-info">
            <h2 class="title">FACTURE</h2>
            <div class="fact-meta">
                <div>N° {{ $facture->numero }}</div>
                <div>{{ $company->city ?? '' }}, le {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</div>
            </div>
            <div class="chip small" style="margin-top:6px">
                Émetteur : <strong>{{ $companyName }}</strong>
            </div>
        </div>
    </div>

    {{-- ============ CLIENT + SOCIÉTÉ LÉGALES (compact side-by-side) ============ --}}
    <div class="row section no-break" style="gap:14px;">
        <div class="col">
            <div class="section-title">Client</div>
            <div class="tight">
                <strong>{{ $displayName }}</strong><br>
                @if($client)
                    @if($addr1) {{ $addr1 }}<br>@endif
                    @if($addr2) {{ $addr2 }}<br>@endif
                    @if($client->email) {{ $client->email }}<br>@endif
                    @if($client->telephone) {{ $client->telephone }}@endif
                @else
                    @if($prospectEmail) Email : {{ $prospectEmail }}<br>@endif
                    @if($prospectPhone) Tél. : {{ $prospectPhone }}@endif
                @endif
            </div>
        </div>

    {{-- ============ VÉHICULE / SINISTRE / ASSURANCE (tight) ============ --}}
    @if($client)
    <div class="section no-break">
        <div class="section-title">Véhicule / Sinistre / Assurance</div>
        <table class="kv">
            <tr>
                <th>Plaque d'immatriculation</th>
                <td>{{ $client->plaque ?: '—' }}</td>
                <th>Kilométrage</th>
                <td>{{ $fmtKm($client->kilometrage) ?: '—' }}</td>
            </tr>
      
            <tr>
                <th>Type de vitrage</th>
                <td>{{ $client->type_vitrage ?: '—' }}</td>
                <th>Réparation</th>
                <td>
                    @if(!is_null($client->reparation))
                        {{ (string)$client->reparation === '1' ? 'Oui' : 'Non' }}
                    @else — @endif
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
                <th>N° de sinistre</th>
                <td>{{ $client->numero_sinistre ?: '—' }}</td>
            </tr>
         
        </table>
    </div>
    @endif

    {{-- ============ DÉTAILS DES PRESTATIONS ============ --}}
    <div class="section no-break">
        <div class="section-title">Détails des prestations</div>
        <table class="items">
            <thead>
            <tr>
                <th>Description</th>
                <th class="right">Prix unitaire</th>
                <th class="right">Qté</th>
                <th class="right">Montant HT</th>
            </tr>
            </thead>
            <tbody>
            @foreach($facture->items as $item)
                <tr>
                    <td>
                        <div>{{ $item->produit }}</div>
                        @if($item->description)
                            <div class="muted small">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="right">{{ number_format((float)$item->prix_unitaire, 2, ',', ' ') }} €</td>
                    <td class="right">{{ rtrim(rtrim(number_format((float)$item->quantite, 2, ',', ' '), '0'), ',') }}</td>
                    <td class="right">{{ number_format((float)$item->total_ht, 2, ',', ' ') }} €</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <table class="totals no-break" style="margin-top:8px;">
            <tr>
                <td>Total HT</td>
                <td class="right">{{ number_format((float)$facture->total_ht, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td>TVA</td>
                <td class="right">{{ number_format((float)$facture->total_tva, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td><strong>Total TTC</strong></td>
                <td class="right"><strong>{{ number_format((float)$facture->total_ttc, 2, ',', ' ') }} €</strong></td>
            </tr>
        </table>
    </div>

    {{-- ============ MODALITÉS (compact) ============ --}}
    <div class="section no-break">
        <div class="section-title">Modalités & conditions de règlement</div>
        <div class="terms small">{!! nl2br(e($termsText)) !!}</div>
    </div>

    {{-- ============ FOOTER (one line) ============ --}}
    <div class="footer no-break">
        {{ $companyName }}
        @if($company?->legal_form) — Forme : {{ $company->legal_form }} @endif
        @if(!is_null($company?->capital)) — Capital : {{ number_format((float)$company->capital, 0, ',', ' ') }} € @endif
        @if($company?->siret) — SIRET : {{ $company->siret }} @endif
        @if($company?->tva) — TVA : {{ $company->tva }} @endif
        @if($company?->ape || $company?->naf_code) — NAF/APE : {{ $company->ape ?? $company->naf_code }} @endif
        @if($company?->rcs_number || $company?->rcs_city) — RCS : {{ $company->rcs_number }} {{ $company->rcs_city }} @endif
    </div>
</div>
</body>
</html>