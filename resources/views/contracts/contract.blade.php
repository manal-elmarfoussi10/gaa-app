{{-- resources/views/contracts/contract.blade.php --}}
@php
  /** @var \App\Models\Client $client */
  $company = $company ?? $client->company;

  // --- Company (with fallbacks) ---
  $cName   = $company->commercial_name ?? $company->name ?? 'GS Auto';
  $cAddr   = trim(($company->address ? $company->address.', ' : '').($company->postal_code ?? '').' '.($company->city ?? ''));
  $cPhone  = $company->phone ?? '';
  $cMail   = $company->email ?? '';
  $cSiret  = $company->siret ?? '';
  $cTva    = $company->tva ?? '';

  // --- GS Auto logo (local first, else remote) ---
  $gsLocal = public_path('images/GS.png');
  $gsLogo  = file_exists($gsLocal) ? $gsLocal : 'https://dev.gservicesauto.com/images/GS.png';

  // --- Palette (subtle orange) ---
  $ORANGE      = '#F97316';   // accents
  $ORANGE_SOFT = '#FFF7ED';   // soft bg
  $ORANGE_LINE = '#FDBA74';   // borders
  $INK         = '#111827';

  // --- Client helpers ---
  $fullName = $client->nom_complet
    ?? trim(($client->prenom ?? '').' '.($client->nom_assure ?? $client->nom ?? ''));

  // Short helpers
  $fmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
@endphp
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Contrat GS Auto — {{ $fullName }}</title>
  <style>
    @page { margin: 32px 36px; }
    body  { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: {{ $INK }}; font-size:12px; line-height:1.45; }

    /* Header / brand */
    .brand { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
    .brand__left { display:flex; align-items:center; gap:14px; }
    .brand__name { font-weight:900; font-size:18px; color:#1F2937; }
    .brand__meta { font-size:10px; color:#4B5563; line-height:1.35; }
    .brand__tag  { font-weight:900; font-size:22px; color:#1F2937; }

    .logo { width:82px; height:82px; object-fit:contain; border-radius:10px; border:1px solid #E5E7EB; background:#fff; }

    .badge {
      display:inline-block; padding:4px 10px; border-radius:999px;
      background: {{ $ORANGE_SOFT }}; color: {{ $ORANGE }}; border:1px solid {{ $ORANGE_LINE }};
      font-weight:800; font-size:11px; text-transform:uppercase; letter-spacing:.02em;
    }

    /* blocks */
    .card { border:1px solid #E5E7EB; border-radius:10px; padding:12px 14px; margin-bottom:10px; }
    .card.orange { border-color: {{ $ORANGE_LINE }}; background: {{ $ORANGE_SOFT }}; }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
    .grid-3 { display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; }

    /* headings */
    h1 { font-size:20px; margin:0 0 8px 0; color:#0F172A; }
    h2 { font-size:14px; margin:0 0 8px 0; color:#0F172A; }
    .section-title {
      margin:12px 0 8px; font-size:13px; font-weight:900; color: {{ $ORANGE }};
      text-transform:uppercase; letter-spacing:.02em;
    }
    .muted { color:#6B7280; }
    .small { font-size:10px; }

    /* tables */
    table.meta { width:100%; border-collapse:separate; border-spacing:0; }
    table.meta th, table.meta td { padding:8px 10px; font-size:12px; vertical-align:top; border-bottom:1px solid #E5E7EB; }
    table.meta th { width:180px; color:#374151; font-weight:700; background:#F9FAFB; }
    table.meta td .pill { display:inline-block; padding:2px 8px; border:1px solid #E5E7EB; border-radius:999px; font-size:11px; background:#fff; }

    /* legal text blocks */
    .block {
      border:1px dashed {{ $ORANGE_LINE }}; background: {{ $ORANGE_SOFT }};
      border-radius:10px; padding:12px 14px; margin-top:8px;
    }
    .legal p { margin:0 0 8px; text-align:justify; }

    /* signature boxes */
    .sign-grid { display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-top:12px; }
    .sign-box {
      border:2px dashed {{ $ORANGE_LINE }}; border-radius:12px; padding:14px 16px; min-height:140px; background:#FFFFFF;
    }
    .sign-box h3 { margin:0 0 6px; font-size:14px; color:#0F172A; }
    .sign-row { margin:4px 0; color:#1F2937; }
    .sign-line { display:inline-block; min-width:160px; border-bottom:2px solid {{ $ORANGE_LINE }}; transform: translateY(-3px); }
    .sign-hint { font-size:11px; color:#64748B; margin-top:8px; }

    .footer { margin-top:16px; padding-top:10px; border-top:2px solid {{ $ORANGE_LINE }}; font-size:10px; color:#6B7280; }

    /* Yousign smart anchors (keep in DOM) */
    .y-anchor { font-size:1px; color:#ffffff; }
  </style>
</head>
<body>

  {{-- Header / branding --}}
  <div class="brand">
    <div class="brand__left">
      <img class="logo" src="{{ $gsLogo }}" alt="GS Auto logo">
      <div>
        <div class="brand__name">{{ $cName }}</div>
        <div class="brand__meta">
          {{ $cAddr }}<br>
          @if($cPhone) Tél&nbsp;: {{ $cPhone }} · @endif
          @if($cMail) Email&nbsp;: {{ $cMail }} @endif
          @if($cSiret) · SIRET {{ $cSiret }} @endif
          @if($cTva) · TVA {{ $cTva }} @endif
        </div>
      </div>
    </div>
    <div style="text-align:right">
      <div class="badge">Contrat & cession de créance</div><br>
      <div class="brand__tag">GS Auto</div>
    </div>
  </div>

  {{-- Sub-meta --}}
  <div class="small muted" style="margin-bottom:10px;">
    Contrat n° {{ $client->id }} · Édité le {{ now()->format('d/m/Y') }}
  </div>

  {{-- 1) Identité / véhicule / assurance --}}
  <div class="grid">
    <div class="card">
      <div class="section-title">Client</div>
      <table class="meta">
        <tr><th>Nom</th><td>{{ $fullName }}</td></tr>
        <tr><th>Email</th><td>{{ $client->email ?? '—' }}</td></tr>
        <tr><th>Téléphone</th><td>{{ $client->telephone ?? '—' }}</td></tr>
        <tr><th>Adresse</th><td>{{ $client->adresse ?? '—' }}</td></tr>
        <tr><th>Réf. interne</th><td>{{ $client->reference_interne ?? '—' }}</td></tr>
        <tr><th>Réf. client</th><td>{{ $client->reference_client ?? '—' }}</td></tr>
      </table>
    </div>
    <div class="card">
      <div class="section-title">Véhicule</div>
      <table class="meta">
        <tr><th>Immatriculation</th><td>{{ $client->plaque ?? '—' }}</td></tr>
        <tr><th>Kilométrage</th><td>{{ $client->kilometrage ? number_format($client->kilometrage,0,',',' ') . ' km' : '—' }}</td></tr>
        <tr><th>Type de vitrage</th><td>{{ $client->type_vitrage ?? '—' }}</td></tr>
        <tr><th>Anc. modèle plaque</th><td>{{ $client->ancien_modele_plaque ?? '—' }}</td></tr>
        <tr><th>Adresse de pose</th><td>{{ $client->adresse_pose ?? '—' }}</td></tr>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="section-title">Assurance</div>
    <table class="meta">
      <tr><th>Compagnie</th><td>{{ $client->nom_assurance ?? '—' }}</td></tr>
      <tr><th>N° de police</th><td>{{ $client->numero_police ?? '—' }}</td></tr>
      <tr><th>N° de sinistre</th><td>{{ $client->numero_sinistre ?? '—' }}</td></tr>
      <tr><th>Autre assurance</th><td>{{ $client->autre_assurance ?? '—' }}</td></tr>
      <tr><th>Date du sinistre</th><td>{{ $fmt($client->date_sinistre) }}</td></tr>
      <tr><th>Date de déclaration</th><td>{{ $fmt($client->date_declaration) }}</td></tr>
    </table>
  </div>

  {{-- 2) Déclaration de bris de glace & ordre de réparation --}}
  <div class="card orange">
    <div class="section-title">Déclaration de bris de glace & ordre de réparation</div>
    <table class="meta">
      <tr>
        <th>Lieu</th>
        <td>{{ $client->adresse_pose ?: '________________' }}</td>
      </tr>
      <tr>
        <th>Date</th>
        <td>{{ $fmt($client->date_sinistre) }}</td>
      </tr>
      <tr>
        <th>Circonstances</th>
        <td>{{ $client->raison ?: 'Projection de gravillons / choc / autre (préciser) ____________________' }}</td>
      </tr>
      <tr>
        <th>Dégâts apparents</th>
        <td>
          <span class="pill">Pare-brise</span>
          <span class="pill">Latérale</span>
          <span class="pill">Lunette AR</span>
          <span class="pill">Capteurs / Caméra</span>
          <span class="pill">Autre&nbsp;: ________</span>
        </td>
      </tr>
      <tr>
        <th>Franchise</th>
        <td>Montant estimé&nbsp;: ______ € — <span class="muted">le solde éventuel reste dû par le client</span></td>
      </tr>
    </table>
  </div>

  {{-- 3) Mandat & Cession de créance (notification) --}}
  <div class="grid">
    <div class="block legal">
      <h2>Mandat</h2>
      <p>
        Le client mandate {{ $cName }} pour accomplir auprès de la compagnie d’assurance toutes
        démarches nécessaires à la gestion du sinistre et au règlement direct des travaux de
        remplacement/réparation de vitrage effectués sur le véhicule identifié ci-dessus.
      </p>
    </div>
    <div class="block legal">
      <h2>Cession de créance — Notification</h2>
      <p>
        En cas de prise en charge, le client cède à {{ $cName }} l’intégralité de ses droits et actions
        relatifs à l’indemnisation du sinistre afin de permettre le paiement direct du montant de la
        facture émise par {{ $cName }}. La présente cession est notifiée à l’assureur par {{ $cName }}.
        Le solde éventuel (franchise / exclusions) reste à la charge du client.
      </p>
    </div>
  </div>

  {{-- 4) Convention de règlement des réparations (articles) --}}
  <div class="card legal">
    <h2>Convention de règlement des réparations</h2>
    <p><strong>Art. 1 — Remise du véhicule.</strong> Le véhicule est confié à {{ $cName }} pour remise en état conformément aux règles de l’art et à l’ordre de réparation.</p>
    <p><strong>Art. 2 — Travaux & garantie.</strong> {{ $cName }} exécute les travaux nécessaires. Le client prévient son assureur et déclare bénéficier d’une garantie adaptée.</p>
    <p><strong>Art. 3 — Prix & paiement.</strong> Le client s’engage à régler les travaux. Les sommes dues par l’assureur sont imputées par compensation sur la facture.</p>
    <p><strong>Art. 4 — Cession de créance.</strong> La cession permet le règlement direct à {{ $cName }} par l’assureur. Le solde non garanti reste dû par le client.</p>
    <p><strong>Art. 5 — Effets.</strong> La cession emporte transfert des droits et accessoires. {{ $cName }} peut solliciter le paiement direct en lieu et place du client.</p>
    <p><strong>Art. 6 — Garantie du cédant.</strong> Le client garantit l’existence et la validité de la créance. En cas de refus/insuffisance de prise en charge, il indemnise {{ $cName }} à hauteur du préjudice (au minimum le solde de facture).</p>
    <p><strong>Art. 7 — Inexécution.</strong> En cas de défaillance de l’assureur ou du client, {{ $cName }} pourra agir contre l’un ou l’autre, demander la résolution et/ou faire valoir ses droits.</p>
    <p><strong>Art. 8 — Effets de la résolution.</strong> La résolution imputable au client/assureur peut ouvrir droit à indemnisation. À titre de garantie, un privilège peut être inscrit sur le véhicule pour le solde restant dû.</p>
  </div>

  {{-- 5) Protection des données (RGPD) & informations --}}
  <div class="card">
    <div class="section-title">Informations & protection des données</div>
    <p class="small muted" style="margin:0 0 6px">
      Les données collectées sont nécessaires au traitement du sinistre et à l’exécution des prestations.
      Elles peuvent être transmises à la compagnie d’assurance et à nos partenaires techniques strictement
      habilités. Conformément au RGPD, vous disposez d’un droit d’accès, de rectification et d’opposition
      en écrivant à {{ $cMail ?: 'notre service' }}. Les pièces justificatives pourront être conservées pendant la durée
      légale de conservation.
    </p>
  </div>

  {{-- 6) Signatures (avec ancres Yousign) --}}
  <div class="sign-grid">
    <div class="sign-box">
      <h3>Signature du client</h3>
      <div class="sign-row">Nom : <strong>{{ $fullName }}</strong></div>
      <div class="sign-row">
        Fait à : <span class="sign-line">&nbsp;</span>
        &nbsp;&nbsp;le : {{ now()->format('d/m/Y') }}
      </div>
      <div class="sign-hint">Lu et approuvé</div>
      <div class="y-anchor">[[SIGN_CLIENT]]</div>
    </div>

    <div class="sign-box">
      <h3>Cachet & signature de {{ $cName }}</h3>
      <div class="sign-row">Représentant : <span class="sign-line">&nbsp;</span></div>
      <div class="sign-row">
        Fait à : <span class="sign-line">&nbsp;</span>
        &nbsp;&nbsp;le : {{ now()->format('d/m/Y') }}
      </div>
      <div class="y-anchor">[[SIGN_COMPANY]]</div>
    </div>
  </div>

  {{-- Footer --}}
  <div class="footer">
    Document généré automatiquement — {{ $cName }} · {{ $cAddr }}
    @if($cPhone) · {{ $cPhone }} @endif
    @if($cMail) · {{ $cMail }} @endif
  </div>

</body>
</html>