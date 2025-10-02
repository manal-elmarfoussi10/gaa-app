{{-- resources/views/contracts/contract.blade.php --}}
@php
    /** @var \App\Models\Client $client */
    $company = $client->company;

    // Company fallbacks to avoid blanks
    $cName   = $company->commercial_name ?: ($company->name ?? 'Votre Société');
    $cAddr   = trim(($company->address ? $company->address.' ' : '')
            .($company->postal_code ? $company->postal_code.' ' : '')
            .($company->city ?? ''));
    $cEmail  = $company->email ?? '';
    $cPhone  = $company->phone ?? '';
    $cSiret  = $company->siret ?? '';
    $cTva    = $company->tva ?? '';
    $cApe    = $company->ape ?? $company->naf_code ?? '';
    $cLogo   = $company->logo ?? null; // store relative path (public disk)
    $logoUrl = $cLogo ? (Storage::disk('public')->exists($cLogo) ? storage_path('app/public/'.$cLogo) : null) : null;

    // Client helpers
    $fullName = trim(($client->prenom ?? '').' '.($client->nom_assure ?? $client->nom ?? ''));
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Contrat GS Auto – {{ $fullName ?: 'Client' }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    @page { margin: 28mm 18mm 28mm 18mm; }
    body { font-family: DejaVu Sans, sans-serif; color:#111; font-size:12px; line-height:1.45; }
    h1,h2,h3 { margin:0 0 6px; }
    h1 { font-size:22px; letter-spacing: .3px; }
    h2 { font-size:14px; text-transform:uppercase; color:#444; letter-spacing: .4px; }
    small, .muted { color:#666; }
    .grid { display: table; width: 100%; table-layout: fixed; }
    .col { display: table-cell; vertical-align: top; }
    .w-50 { width: 50%; }
    .mt-2 { margin-top: 8px; } .mt-3 { margin-top: 12px; } .mt-4 { margin-top: 18px; } .mt-5 { margin-top: 24px; }
    .mb-0 { margin-bottom: 0; } .mb-1{ margin-bottom: 4px; } .mb-2 { margin-bottom: 8px; } .mb-3 { margin-bottom: 12px; }
    .p-0{ padding:0; } .p-2{ padding:8px; } .p-3{ padding:12px; } .p-4{ padding:16px; }
    .card { border:1px solid #cfcfcf; border-radius:6px; }
    .card h2 { background:#f4f6f8; border-bottom:1px solid #e5e7ea; padding:8px 12px; }
    .card .card-body { padding:10px 12px; }
    .kv { display: table; width:100%; }
    .kv .k { color:#555; width: 38%; display: table-cell; padding: 6px 8px; border-bottom:1px solid #f0f0f0; }
    .kv .v { width: 62%; display: table-cell; padding: 6px 8px; border-bottom:1px solid #f0f0f0; }
    .muteborder { border:1px dashed #d9d9d9; }
    .text-right { text-align:right; }
    .text-center { text-align:center; }
    .badge { display:inline-block; padding:2px 8px; border-radius:10px; background:#eef6ff; color:#0b68c7; font-size:11px; }
    .table { width:100%; border-collapse: collapse; }
    .table th, .table td { border:1px solid #dfe3e8; padding:8px; }
    .table th { background:#f8f9fb; font-weight:700; color:#3a3a3a; }
    .totals td { border: none; padding:6px 8px; }
    .hr { height:1px; background:#e7e7e7; border:0; margin:18px 0; }
    .signature-box { height:88px; border:1px solid #cfd8dc; border-radius:4px; background:#fff; }
    .page-footer, .page-header { position: fixed; left: 0; right: 0; color:#777; }
    .page-header { top: -18mm; }
    .page-footer { bottom: -16mm; font-size: 11px; }
    .page-number:before { content: counter(page); }
    .company-block { line-height:1.3; }
    .company-name { font-size:18px; font-weight:700; letter-spacing:.3px; }
    .tiny { font-size:11px; }
</style>
</head>
<body>

{{-- ====== HEADER ====== --}}
<div class="page-header">
    <div class="grid">
        <div class="col w-50">
            <div class="company-block">
                <div class="company-name">{{ $cName }}</div>
                @if($cAddr)<div>{{ $cAddr }}</div>@endif
                <div class="tiny">
                    @if($cEmail) ✉ {{ $cEmail }}@endif
                    @if($cPhone) · ☎ {{ $cPhone }}@endif
                </div>
                <div class="tiny">
                    @if($cSiret) SIRET: {{ $cSiret }}@endif
                    @if($cTva) · TVA: {{ $cTva }}@endif
                    @if($cApe) · APE/NAF: {{ $cApe }}@endif
                </div>
            </div>
        </div>
        <div class="col w-50" style="text-align:right;">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" style="height:44px;">
            @endif
        </div>
    </div>
    <hr class="hr">
</div>

{{-- ====== FOOTER ====== --}}
<div class="page-footer">
    <hr class="hr">
    <div class="grid">
        <div class="col w-50 tiny">
            Contrat GS Auto — {{ $cName }}
        </div>
        <div class="col w-50 tiny text-right">
            Page <span class="page-number"></span>
        </div>
    </div>
</div>

{{-- ====== CONTENT ====== --}}
<main>
    <h1 class="mb-0">Contrat GS Auto</h1>
    <div class="muted">Référence dossier <strong>#{{ $client->id }}</strong></div>

    <div class="grid mt-3">
        <div class="col w-50">
            <div class="badge">Date : {{ now()->format('d/m/Y') }}</div>
        </div>
        <div class="col w-50 text-right">
            @if($client->statut_gsauto)
                <span class="badge">Statut : {{ $client->statut_gsauto }}</span>
            @endif
        </div>
    </div>

    {{-- Client --}}
    <div class="card mt-4">
        <h2>Client</h2>
        <div class="card-body">
            <div class="kv">
                <div class="k">Nom / Prénom</div><div class="v">{{ $fullName ?: '—' }}</div>
                <div class="k">Email</div><div class="v">{{ $client->email ?: '—' }}</div>
                <div class="k">Téléphone</div><div class="v">{{ $client->telephone ?: '—' }}</div>
                <div class="k">Adresse</div><div class="v">
                    @php
                        $addr = trim(($client->adresse ?? '').' '.($client->code_postal ?? '').' '.($client->ville ?? ''));
                    @endphp
                    {{ $addr ?: '—' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Véhicule --}}
    <div class="card mt-3">
        <h2>Véhicule</h2>
        <div class="card-body">
            <div class="kv">
                <div class="k">Immatriculation</div><div class="v">{{ $client->plaque ?: '—' }}</div>
                <div class="k">Type de vitrage</div><div class="v">{{ $client->type_vitrage ?: '—' }}</div>
                <div class="k">Kilométrage</div><div class="v">{{ $client->kilometrage ? number_format($client->kilometrage, 0, ',', ' ').' km' : '—' }}</div>
                <div class="k">Ancien modèle plaque</div><div class="v">{{ $client->ancien_modele_plaque ?: '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Assurance & Sinistre --}}
    <div class="card mt-3">
        <h2>Assurance & sinistre</h2>
        <div class="card-body">
            <div class="kv">
                <div class="k">Assureur</div><div class="v">{{ $client->nom_assurance ?: ($client->autre_assurance ?: '—') }}</div>
                <div class="k">N° police</div><div class="v">{{ $client->numero_police ?: '—' }}</div>
                <div class="k">N° sinistre</div><div class="v">{{ $client->numero_sinistre ?: '—' }}</div>
                <div class="k">Date du sinistre</div><div class="v">
                    {{ $client->date_sinistre ? \Carbon\Carbon::parse($client->date_sinistre)->format('d/m/Y') : '—' }}
                </div>
                <div class="k">Date de déclaration</div><div class="v">
                    {{ $client->date_declaration ? \Carbon\Carbon::parse($client->date_declaration)->format('d/m/Y') : '—' }}
                </div>
                <div class="k">Adresse d’intervention</div><div class="v">{{ $client->adresse_pose ?: '—' }}</div>
                <div class="k">Précisions</div><div class="v">{{ $client->precision ?: '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Autorisations / Déclarations --}}
    <div class="card mt-3">
        <h2>Autorisations & déclarations</h2>
        <div class="card-body">
            <ul style="margin:6px 0 0 18px; padding:0;">
                <li>J’autorise <strong>{{ $cName }}</strong> à intervenir sur mon véhicule pour la réparation / pose de vitrage décrite ci-dessus.</li>
                <li>Je confirme l’exactitude des informations communiquées (assurance, police, sinistre, identité, véhicule).</li>
                <li>J’autorise la transmission de mes documents (carte grise, carte verte, facture, photos) à l’assureur et partenaires, aux seules fins de gestion du dossier.</li>
                <li>En cas de prise en charge, je cède à <strong>{{ $cName }}</strong> la créance due par l’assureur à hauteur des montants remboursables (cession de créance).</li>
                <li>Je reconnais avoir pris connaissance des conditions générales et de l’information RGPD en fin de document.</li>
            </ul>
        </div>
    </div>

    {{-- Détail (section récap — non obligatoire si vous joignez le devis/la facture) --}}
    <div class="card mt-3">
        <h2>Détail de prestation (récapitulatif)</h2>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:50%;">Désignation</th>
                        <th style="width:15%;">Quantité</th>
                        <th style="width:17%;">PU HT (€)</th>
                        <th style="width:18%;">Total HT (€)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Remplacement/pose vitrage (référence)</td>
                        <td class="text-center">1</td>
                        <td class="text-right">—</td>
                        <td class="text-right">—</td>
                    </tr>
                </tbody>
            </table>

            <table class="totals" style="margin-left:auto; margin-top:8px;">
                <tr>
                    <td class="text-right">Total HT</td>
                    <td style="min-width:90px;" class="text-right">—</td>
                </tr>
                <tr>
                    <td class="text-right">TVA (20%)</td>
                    <td class="text-right">—</td>
                </tr>
                <tr>
                    <td class="text-right"><strong>Total TTC</strong></td>
                    <td class="text-right"><strong>—</strong></td>
                </tr>
            </table>
            <div class="tiny mt-2 muted">Le chiffrage définitif figure sur le devis ou la facture correspondante.</div>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="grid mt-5">
        <div class="col w-50">
            <strong>Signature du client</strong> @if($fullName) — <span class="muted">{{ $fullName }}</span>@endif
            <div class="signature-box mt-2"></div>
            <div class="tiny mt-1">Date et lieu : {{ now()->format('d/m/Y') }} — ...........................................</div>
        </div>
        <div class="col w-50">
            <strong>Pour {{ $cName }}</strong>
            <div class="signature-box mt-2"></div>
            <div class="tiny mt-1">Nom / Qualité : .........................................................</div>
        </div>
    </div>

    {{-- Conditions générales & RGPD --}}
    <div class="mt-5">
        <h2 class="mb-1">Conditions générales & RGPD</h2>
        <div class="tiny">
            <p class="mb-1"><strong>1. Objet.</strong> Le présent contrat encadre la réparation / le remplacement de vitrages sur le véhicule identifié, réalisée conformément aux règles de l’art et aux prescriptions constructeur.</p>
            <p class="mb-1"><strong>2. Prix & paiement.</strong> Les prix sont exprimés en EUR. En cas de prise en charge par l’assurance, le client cède à {{ $cName }} la créance correspondant au montant remboursable. Toute franchise, dépense non prise en charge ou prestation complémentaire reste due par le client.</p>
            <p class="mb-1"><strong>3. Délais.</strong> Les délais d’intervention sont indicatifs. La responsabilité de {{ $cName }} ne saurait être engagée en cas de retard imputable à un tiers (assurance, approvisionnement, etc.).</p>
            <p class="mb-1"><strong>4. Garantie.</strong> Les interventions bénéficient d’une garantie pièces et main-d’œuvre selon la législation et nos conditions internes (défauts de pose hors choc / usage inapproprié).</p>
            <p class="mb-1"><strong>5. Responsabilité.</strong> {{ $cName }} est tenue d’une obligation de moyens. Toute réclamation doit être formulée par écrit sous 7 jours ouvrés suivant l’intervention.</p>
            <p class="mb-1"><strong>6. Données personnelles (RGPD).</strong> Les données recueillies sont nécessaires à la gestion du dossier (RDV, relation assurance, facturation). Conservation pendant la durée légale; accès réservé aux personnes habilitées. Droits d’accès, rectification, opposition, effacement et portabilité par mail à {{ $cEmail ?: 'votre-email@domaine.tld' }}.</p>
            <p class="mb-1"><strong>7. Litiges.</strong> À défaut d’accord amiable, compétence exclusive des tribunaux du siège social. Droit français applicable.</p>
        </div>
    </div>
</main>

</body>
</html>