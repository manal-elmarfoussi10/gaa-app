@php
/**
 * Expected:
 * - $client  : App\Models\Client (with ->company loaded)
 * - $company : App\Models\Company
 *
 * IMPORTANT for DomPDF images:
 *   Always pass absolute file paths (public_path/storage/...) or data URIs.
 */
$company = $company ?? $client->company;

// Helpers to resolve absolute image paths for DomPDF
$logoPath = null;
if (!empty($company?->logo)) {
    $candidate = public_path('storage/'.$company->logo);
    $logoPath  = is_file($candidate) ? $candidate : null;
}

$companySignPath = null;
if (!empty($company?->signature_path)) {
    $candidate2 = public_path('storage/'.$company->signature_path);
    $companySignPath = is_file($candidate2) ? $candidate2 : null;
}

$clientSignPath = null;
if (!empty($client?->signature_path)) {
    $candidate3 = public_path('storage/'.$client->signature_path);
    $clientSignPath = is_file($candidate3) ? $candidate3 : null;
}

// Formatting helpers
$fullName = trim(($client->prenom ?? '').' '.($client->nom_assure ?? $client->nom ?? ''));
$today    = now()->timezone(config('app.timezone','Europe/Paris'))->format('d/m/Y');

$addressClient = trim(($client->adresse ?? '')
    . (empty($client->code_postal) ? '' : ' '.$client->code_postal)
    . (empty($client->ville) ? '' : ' '.$client->ville));

$companyAddress = trim(($company->address ?? '')
    . (empty($company->postal_code) ? '' : ' '.$company->postal_code)
    . (empty($company->city) ? '' : ' '.$company->city));
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Contrat GS Auto – {{ $fullName ?: 'Client' }}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
  @page { margin: 28mm 18mm 30mm 18mm; }
  body  { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; font-size:12px; }

  .header { position: fixed; top: -18mm; left: 0; right: 0; height: 58mm; }
  .footer { position: fixed; bottom: -18mm; left: 0; right: 0; height: 40mm; font-size:10px; color:#555; }

  .brand { display:flex; gap:14px; align-items:center; }
  .brand img { height:58px; }
  .brand h1 { font-size:20px; margin:0; letter-spacing: .2px; }

  .meta { margin-top:6px; line-height:1.35; }
  .hr   { height:1px; background:#222; margin:8px 0 12px; }

  .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  .grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }

  .card { border:1px solid #555; border-radius:6px; padding:10px 12px; }
  .card h3 { margin:0 0 8px 0; font-size:13px; text-transform:uppercase; letter-spacing:.4px; }
  .label { color:#666; font-size:11px; }
  .value { font-weight:600; }

  .table { width:100%; border-collapse: collapse; margin-top:6px; }
  .table th, .table td { border:1px solid #777; padding:6px 8px; }
  .table th { background:#f2f2f2; text-align:left; }

  .muted { color:#666; }
  .small { font-size:10px; }
  .mt-6 { margin-top: 6px; }
  .mt-10{ margin-top:10px; }
  .mt-14{ margin-top:14px; }
  .mt-18{ margin-top:18px; }
  .mt-24{ margin-top:24px; }
  .mb-0 { margin-bottom:0; }
  .center { text-align:center; }
  .right  { text-align:right; }

  .signatures { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-top:18px; }
  .sig-box { border:1px dashed #888; border-radius:6px; padding:10px; min-height:120px; }
  .sig-title { font-size:12px; font-weight:600; margin-bottom:6px; }
  .sig-line  { margin-top:30px; border-top:1px solid #888; }

  .terms { font-size:10.5px; line-height:1.45; }
  .page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- ================= Header ================= --}}
<div class="header">
  <div class="brand">
    @if($logoPath)
      <img src="{{ $logoPath }}" alt="Logo">
    @endif
    <div>
      <h1>Contrat GS Auto</h1>
      <div class="meta">
        <div><strong>{{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }}</strong></div>
        @if($companyAddress)<div>{{ $companyAddress }}</div>@endif
        <div>
          @if($company?->email) ✉ {{ $company->email }} @endif
          @if($company?->phone) · ☎ {{ $company->phone }} @endif
        </div>
        <div class="small muted">
          @if($company?->siret) SIRET: {{ $company->siret }} @endif
          @if($company?->tva) · TVA: {{ $company->tva }} @endif
          @if($company?->rcs_number) · RCS: {{ $company->rcs_number }} {{ $company->rcs_city }} @endif
        </div>
      </div>
    </div>
  </div>
  <div class="hr"></div>
</div>

{{-- ================= Footer ================= --}}
<div class="footer">
  <div class="hr"></div>
  <div class="small muted">
    <div><strong>{{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }}</strong> — {{ $companyAddress }}</div>
    <div>
      @if($company?->email) ✉ {{ $company->email }} @endif
      @if($company?->phone) · ☎ {{ $company->phone }} @endif
      @if($company?->tva) · TVA: {{ $company->tva }} @endif
      @if($company?->ape) · Code APE: {{ $company->ape }} @endif
    </div>
    <div class="right">Page <span class="page-number"></span></div>
  </div>
</div>

<script type="text/php">
if (isset($pdf)) {
    $pdf->page_text(520, 805, "Page {PAGE_NUM} / {PAGE_COUNT}", "DejaVuSans", 9, array(0,0,0));
}
</script>

{{-- ================= Body ================= --}}
<main>

  {{-- ===== Reference / Date ===== --}}
  <div class="grid-3 mt-14">
    <div class="card">
      <div class="label">Référence dossier</div>
      <div class="value">#{{ $client->id }}</div>
    </div>
    <div class="card">
      <div class="label">Date</div>
      <div class="value">{{ $today }}</div>
    </div>
    <div class="card">
      <div class="label">Statut GS Auto</div>
      <div class="value">{{ $client->statut_gsauto ?? '—' }}</div>
    </div>
  </div>

  {{-- ===== Client ===== --}}
  <div class="card mt-14">
    <h3>Client</h3>
    <table class="table">
      <tr>
        <th>Nom / Prénom</th>
        <td>{{ $fullName ?: '—' }}</td>
      </tr>
      <tr>
        <th>Email</th>
        <td>{{ $client->email ?: '—' }}</td>
      </tr>
      <tr>
        <th>Téléphone</th>
        <td>{{ $client->telephone ?: '—' }}</td>
      </tr>
      <tr>
        <th>Adresse</th>
        <td>{{ $addressClient ?: '—' }}</td>
      </tr>
    </table>
  </div>

  {{-- ===== Véhicule ===== --}}
  <div class="card mt-10">
    <h3>Véhicule</h3>
    <table class="table">
      <tr>
        <th>Immatriculation</th>
        <td>{{ $client->plaque ?: '—' }}</td>
      </tr>
      <tr>
        <th>Type de vitrage</th>
        <td>{{ $client->type_vitrage ?: '—' }}</td>
      </tr>
      <tr>
        <th>Kilométrage</th>
        <td>{{ $client->kilometrage ? number_format($client->kilometrage, 0, ',', ' ') . ' km' : '—' }}</td>
      </tr>
      <tr>
        <th>Ancien modèle plaque</th>
        <td>{{ $client->ancien_modele_plaque ?: '—' }}</td>
      </tr>
    </table>
  </div>

  {{-- ===== Assurance / Sinistre ===== --}}
  <div class="card mt-10">
    <h3>Assurance & Sinistre</h3>
    <table class="table">
      <tr>
        <th>Assureur</th>
        <td>{{ $client->nom_assurance ?: '—' }}</td>
      </tr>
      <tr>
        <th>N° police</th>
        <td>{{ $client->numero_police ?: '—' }}</td>
      </tr>
      <tr>
        <th>N° sinistre</th>
        <td>{{ $client->numero_sinistre ?: '—' }}</td>
      </tr>
      <tr>
        <th>Date du sinistre</th>
        <td>{{ $client->date_sinistre ? \Carbon\Carbon::parse($client->date_sinistre)->format('d/m/Y') : '—' }}</td>
      </tr>
      <tr>
        <th>Date de déclaration</th>
        <td>{{ $client->date_declaration ? \Carbon\Carbon::parse($client->date_declaration)->format('d/m/Y') : '—' }}</td>
      </tr>
      <tr>
        <th>Adresse d’intervention</th>
        <td>{{ $client->adresse_pose ?: '—' }}</td>
      </tr>
      <tr>
        <th>Précisions</th>
        <td>{{ $client->precision ?: '—' }}</td>
      </tr>
    </table>
  </div>

  {{-- ===== Autorisations ===== --}}
  <div class="card mt-10">
    <h3>Autorisations & Déclarations</h3>
    <ul class="terms">
      <li>J’autorise {{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }} à intervenir sur mon véhicule pour la réparation/remplacement du vitrage indiqué.</li>
      <li>Je confirme l’exactitude des informations relatives à mon assurance (compagnie, numéro de police, numéro de sinistre le cas échéant).</li>
      <li>J’autorise la transmission de mes documents (carte grise, carte verte, facture, photos) à mon assureur et partenaires aux seules fins de gestion du dossier.</li>
      <li>En cas de prise en charge assurance, j’accepte la cession de créance à {{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }} à hauteur des sommes dues par l’assureur.</li>
      <li>Je reconnais avoir pris connaissance des conditions générales au verso et de la politique RGPD (voir ci-dessous).</li>
    </ul>
  </div>

  {{-- ===== Tarification (optionnelle) ===== --}}
  <div class="card mt-10">
    <h3>Détail de prestation (HT)</h3>
    <table class="table">
      <thead>
        <tr>
          <th style="width:50%">Désignation</th>
          <th style="width:15%">Quantité</th>
          <th style="width:15%">PU HT (€)</th>
          <th style="width:20%">Total HT (€)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Remplacement/pose vitrage (référence)</td>
          <td class="center">1</td>
          <td class="right">—</td>
          <td class="right">—</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" class="right">Total HT</th>
          <th class="right">—</th>
        </tr>
        <tr>
          <th colspan="3" class="right">TVA (20%)</th>
          <th class="right">—</th>
        </tr>
        <tr>
          <th colspan="3" class="right">Total TTC</th>
          <th class="right">—</th>
        </tr>
      </tfoot>
    </table>
    <div class="small muted mt-6">Le chiffrage définitif figure sur le devis/la facture.</div>
  </div>

  {{-- ===== Signatures ===== --}}
  <div class="signatures mt-18">
    <div class="sig-box">
      <div class="sig-title">Signature du client — {{ $fullName ?: '—' }}</div>
      @if($clientSignPath)
        <img src="{{ $clientSignPath }}" style="height:70px;" alt="Signature client">
      @else
        <div class="muted small">Espace réservé à la signature manuscrite ou électronique.</div>
      @endif
      <div class="sig-line small muted">Date et lieu : {{ $today }} — .......................................</div>
    </div>

    <div class="sig-box">
      <div class="sig-title">Pour {{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }}</div>
      @if($companySignPath)
        <img src="{{ $companySignPath }}" style="height:70px;" alt="Signature société">
      @else
        <div class="muted small">Signature du représentant légal</div>
      @endif
      <div class="sig-line small muted">Cachet de l’entreprise</div>
    </div>
  </div>

  {{-- ===== Annexes photos (si présentes) ===== --}}
  @php
    $docMap = [
      'Photo Vitrage'   => $client->photo_vitrage ?? null,
      'Carte Verte'     => $client->photo_carte_verte ?? null,
      'Carte Grise'     => $client->photo_carte_grise ?? null,
    ];
    $resolved = [];
    foreach ($docMap as $label => $relPath) {
        if (!$relPath) continue;
        $abs = public_path('storage/'.$relPath);
        if (is_file($abs)) $resolved[] = [$label, $abs];
    }
  @endphp

  @if(count($resolved))
    <div class="page-break"></div>
    <h3 class="mt-0">Annexes — Photographies</h3>
    <div class="grid-3 mt-10">
      @foreach($resolved as [$label, $abs])
        <div class="card">
          <div class="label">{{ $label }}</div>
          <img src="{{ $abs }}" alt="{{ $label }}" style="width:100%; height:210px; object-fit:contain;">
        </div>
      @endforeach
    </div>
  @endif

  {{-- ===== Conditions & RGPD ===== --}}
  <div class="page-break"></div>
  <h3 class="mb-0">Conditions générales & RGPD</h3>
  <div class="terms mt-10">
    <p><strong>1. Objet.</strong> Le présent contrat encadre la réparation/le remplacement de vitrages
      sur le véhicule identifié. Les prestations sont réalisées conformément aux règles de l’art et
      prescriptions constructeur.</p>

    <p><strong>2. Prix & paiement.</strong> Les prix sont exprimés en EUR. En cas de prise en charge
      par l’assurance, le client cède à {{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }} la créance
      correspondant au montant remboursable. Toute franchise, dépense non prise en charge ou
      prestation complémentaire reste due par le client.</p>

    <p><strong>3. Délais.</strong> Les délais d’intervention sont indicatifs. {{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }}
      ne peut être tenue responsable d’un retard imputable à un tiers (assurance, approvisionnement, etc.).</p>

    <p><strong>4. Garantie.</strong> Les interventions bénéficient d’une garantie pièces et main d’œuvre
      selon les dispositions légales et nos conditions internes (défauts de pose hors choc/usage inapproprié).</p>

    <p><strong>5. Responsabilité.</strong> {{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }} est tenue d’une obligation
      de moyens. Toute réclamation devra être formulée par écrit dans les 7 jours ouvrés suivant l’intervention.</p>

    <p><strong>6. Données personnelles (RGPD).</strong> Les données recueillies sont nécessaires à la
      gestion du dossier (prise de rendez-vous, relation assurance, facturation). Elles sont conservées
      pendant la durée légale et accessibles par les personnes habilitées. Vous disposez de droits d’accès,
      rectification, opposition, effacement et portabilité en écrivant à
      <em>{{ $company?->email ?: '—' }}</em>. Pour plus d’informations, consultez notre politique de confidentialité.</p>

    <p><strong>7. Règlement des litiges.</strong> À défaut d’accord amiable, compétence exclusive des tribunaux
      du siège social de l’entreprise. Droit français applicable.</p>
  </div>

</main>
</body>
</html>