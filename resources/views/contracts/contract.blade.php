{{-- resources/views/contracts/contract.blade.php --}}
@php
  /** @var \App\Models\Client $client */
  $company = $client->company;

  // ---- Company (fallbacks) ----
  $cName    = $company->commercial_name ?? $company->name ?? 'GS Auto';
  $cAddr    = trim(($company->address ?: '').' '.($company->postal_code ?: '').' '.($company->city ?: ''));
  $cEmail   = $company->email  ?? '';
  $cPhone   = $company->phone  ?? '';
  $cSiret   = $company->siret  ?? '';
  $cTva     = $company->tva    ?? '';
  $cApe     = $company->ape    ?? $company->naf_code ?? '';
  $cRcs     = $company->rcs_number ?? '';
  $cRcsCity = $company->rcs_city ?? '';
  $cLogo    = $company->logo ?? null;

  // Base64 logo if present (public disk)
  $logoBase64 = null;
  try {
      if ($cLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($cLogo)) {
          $logoData  = \Illuminate\Support\Facades\Storage::disk('public')->get($cLogo);
          $mime      = strtolower(pathinfo($cLogo, PATHINFO_EXTENSION)) ?: 'png';
          $logoBase64 = 'data:image/'.$mime.';base64,'.base64_encode($logoData);
      }
  } catch (\Throwable $e) { $logoBase64 = null; }

  // ---- Client ----
  $clientName  = trim(($client->prenom ?: '').' '.($client->nom_assure ?? $client->nom ?? ''));
  $clientEmail = $client->email ?? '';
  $clientPhone = $client->telephone ?? '';
  $clientAddr  = trim(($client->adresse ?: '').' '.($client->code_postal ?: '').' '.($client->ville ?: ''));

  // ---- Vehicle / insurance ----
  $immat    = $client->plaque ?? '';
  $vitrage  = $client->type_vitrage ?? '-';
  $km       = $client->kilometrage ? number_format($client->kilometrage, 0, ',', ' ').' km' : '-';

  $assureur = $client->nom_assurance ?: ($client->autre_assurance ?: '-');
  $police   = $client->numero_police ?: '-';
  $sinistre = $client->numero_sinistre ?: '-';

  $dateSin  = $client->date_sinistre ? \Carbon\Carbon::parse($client->date_sinistre)->format('d/m/Y') : '-';
  $dateDecl = $client->date_declaration ? \Carbon\Carbon::parse($client->date_declaration)->format('d/m/Y') : '-';

  $today    = now()->format('d/m/Y');

  // ---- Colors ----
  $colorPrimary = '#0E7490'; // cyan-700
  $colorDark    = '#0F172A'; // slate-900
  $colorMuted   = '#64748B'; // slate-500
  $borderColor  = '#E2E8F0'; // slate-200
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Contrat {{ $cName }} — Client #{{ $client->id }}</title>
  <style>
    @page { margin: 28mm 20mm 32mm 20mm; }
    body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: {{ $colorDark }}; font-size: 12px; line-height: 1.45; }

    header { position: fixed; top: -20mm; left: 0; right: 0; height: 18mm; }
    footer { position: fixed; bottom: -22mm; left: 0; right: 0; height: 20mm; color: {{ $colorMuted }}; font-size: 10px; }

    .hr { height: 1px; background: {{ $borderColor }}; border: 0; margin: 6px 0 0; }

    .wrap { width: 100%; }
    .flex { display: flex; align-items: center; }
    .space-between { justify-content: space-between; }
    .right { text-align: right; }

    .brand { font-size: 18px; font-weight: 700; color: {{ $colorPrimary }}; }
    .meta { font-size: 11px; color: {{ $colorMuted }}; margin-top: 2px; }

    .title { margin: 10px 0 14px; font-size: 20px; color: {{ $colorPrimary }}; }
    .subtitle { font-size: 13px; color: {{ $colorMuted }}; margin-top: 2px; }

    .badge { display: inline-block; background: {{ $colorPrimary }}; color: #fff; padding: 4px 10px; border-radius: 999px; font-size: 11px; }
    .section { margin-top: 14px; }
    .section h3 { margin: 0 0 8px; font-size: 14px; color: {{ $colorDark }}; }
    .section p { margin: 0 0 6px; }

    .grid-2 { display: table; width: 100%; border-collapse: collapse; }
    .grid-2 .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
    .grid-2 .col:last-child { padding-right: 0; padding-left: 10px; }

    table.info { width: 100%; border-collapse: collapse; }
    table.info th { text-align: left; font-size: 11px; color: {{ $colorMuted }}; font-weight: 600; padding: 6px 8px; background: #F8FAFC; border-bottom: 1px solid {{ $borderColor }}; }
    table.info td { padding: 8px; border-bottom: 1px solid {{ $borderColor }}; }

    .box { border: 1px solid {{ $borderColor }}; border-radius: 6px; padding: 10px 12px; }
    .muted { color: {{ $colorMuted }}; }
    .small { font-size: 10px; }

    .terms ol { margin: 0 0 0 16px; padding: 0; }
    .terms li { margin: 6px 0; }

    .sig-grid { display: table; width: 100%; border-collapse: collapse; margin-top: 16px; }
    .sig-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 12px; }
    .sig-col:last-child { padding-right: 0; padding-left: 12px; }
    .sig-box { border: 1px dashed {{ $colorMuted }}; border-radius: 6px; padding: 12px; height: 120px; }
    .sig-label { font-weight: 600; font-size: 12px; margin-bottom: 6px; color: {{ $colorDark }}; }
    .sig-meta { font-size: 11px; color: {{ $colorMuted }}; }

    .mt-8 { margin-top: 8px; }
    .mt-12 { margin-top: 12px; }
  </style>
</head>
<body>

  {{-- ===== Header ===== --}}
  <header>
    <div class="wrap">
      <div class="flex space-between">
        <div class="flex" style="gap:10px;">
          @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo" style="height:26px;">
          @endif
          <div>
            <div class="brand">{{ $cName }}</div>
            <div class="meta">
              {{ $cAddr }}
              @if($cEmail) · {{ $cEmail }} @endif
              @if($cPhone) · {{ $cPhone }} @endif
            </div>
          </div>
        </div>
        <div class="right">
          <span class="badge">Contrat & Cession de créance</span>
          <div class="meta">Réf. client #{{ $client->id }} — Émis le {{ $today }}</div>
        </div>
      </div>
      <div class="hr"></div>
    </div>
  </header>

  {{-- ===== Footer ===== --}}
  <footer>
    <div class="hr"></div>
    <div class="flex space-between" style="margin-top:6px;">
      <div class="small">
        {{ $cName }}
        @if($cSiret) · SIRET {{ $cSiret }} @endif
        @if($cTva)   · TVA {{ $cTva }} @endif
        @if($cApe)   · APE/NAF {{ $cApe }} @endif
        @if($cRcs || $cRcsCity) · RCS {{ trim($cRcs.' '.$cRcsCity) }} @endif
      </div>
      <div class="small">Page <span class="page-number"></span></div>
    </div>
    <script type="text/php">
      if (isset($pdf)) {
        $x = 535; $y = 816; $text = $PAGE_NUM; $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $pdf->page_text($x, $y, $text, $font, 9, array(100/255, 116/255, 139/255));
      }
    </script>
  </footer>

  {{-- ===== Content ===== --}}
  <main>
    <h1 class="title">Contrat de prise en charge & cession de créance</h1>
    <div class="subtitle">Encadre l’intervention vitrage, l’avance des frais et la cession de créance à l’assurance.</div>

    {{-- Info blocks --}}
    <div class="section grid-2">
      <div class="col">
        <div class="box">
          <h3>Client</h3>
          <table class="info">
            <tr><th>Nom</th><td>{{ $clientName ?: '—' }}</td></tr>
            <tr><th>Email</th><td>{{ $clientEmail ?: '—' }}</td></tr>
            <tr><th>Téléphone</th><td>{{ $clientPhone ?: '—' }}</td></tr>
            <tr><th>Adresse</th><td>{{ $clientAddr ?: '—' }}</td></tr>
          </table>
        </div>
      </div>
      <div class="col">
        <div class="box">
          <h3>Véhicule</h3>
          <table class="info">
            <tr><th>Immatriculation</th><td>{{ $immat ?: '—' }}</td></tr>
            <tr><th>Type de vitrage</th><td>{{ $vitrage }}</td></tr>
            <tr><th>Kilométrage</th><td>{{ $km }}</td></tr>
          </table>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="box">
        <h3>Assurance</h3>
        <table class="info">
          <tr><th>Assureur</th><td>{{ $assureur }}</td></tr>
          <tr><th>N° de police</th><td>{{ $police }}</td></tr>
          <tr><th>N° de sinistre</th><td>{{ $sinistre }}</td></tr>
          <tr><th>Date du sinistre</th><td>{{ $dateSin }}</td></tr>
          <tr><th>Date de déclaration</th><td>{{ $dateDecl }}</td></tr>
        </table>
      </div>
    </div>

    {{-- Objet --}}
    <div class="section">
      <h3>Objet du contrat</h3>
      <p>
        Le présent document formalise l’intervention de <strong>{{ $cName }}</strong> pour la réparation ou le remplacement d’un vitrage sur le véhicule ci-dessus.
        Il encadre l’avance des frais, la facturation directe à l’assureur et, le cas échéant, la <strong>cession de créance</strong> due par l’assureur à l’assuré.
      </p>
    </div>

    {{-- Cession / Mandat --}}
    <div class="section">
      <h3>Cession de créance & mandat de gestion</h3>
      <div class="box">
        <p>
          Je soussigné(e) <strong>{{ $clientName ?: '—' }}</strong>, assuré(e) du véhicule immatriculé <strong>{{ $immat ?: '—' }}</strong>, 
          autorise la société <strong>{{ $cName }}</strong> à gérer, en mon nom, l’ensemble des formalités liées au sinistre susvisé, et à adresser la facture à mon assureur
          ({{ $assureur }} – n° police {{ $police }}).
        </p>
        <p class="mt-8">
          En cas de prise en charge par l’assurance, je <strong>cède à titre de paiement</strong> au profit de <strong>{{ $cName }}</strong> la créance correspondant au montant TTC
          de la facture, à hauteur de l’indemnité due par l’assureur. Le solde éventuel (franchise, exclusions, plafonds, vétusté, défaut de garanties, etc.) reste à ma charge
          et sera réglé à {{ $cName }} à première demande.
        </p>
      </div>
    </div>

    {{-- Conditions générales --}}
    <div class="section terms">
      <h3>Conditions générales</h3>
      <ol>
        <li><strong>Identification :</strong> Le client certifie l’exactitude des informations communiquées (identité, assurance, immatriculation).</li>
        <li><strong>Accord d’intervention :</strong> Le client accepte l’intervention (réparation / remplacement) telle que décrite sur l’ordre de réparation.</li>
        <li><strong>Prix & règlement :</strong> Le tarif appliqué est celui en vigueur le jour de l’intervention. Les montants non pris en charge par l’assureur sont à la charge du client (franchise, options, défaut de garanties, etc.).</li>
        <li><strong>Restitution :</strong> La restitution intervient après l’intervention et, si nécessaire, le règlement des sommes dues par le client.</li>
        <li><strong>Cession de créance :</strong> La cession s’opère à due concurrence des sommes réglées par l’assureur. En cas de refus total ou partiel, le client règle le solde dû.</li>
        <li><strong>Pièces :</strong> Les pièces remplacées peuvent être conservées par {{ $cName }} pour expertise. Propriété réservée jusqu’au paiement intégral.</li>
        <li><strong>Garanties :</strong> Pose vitrage garantie contre les vices de pose selon conditions internes disponibles sur demande.</li>
        <li><strong>Responsabilité :</strong> {{ $cName }} n’est pas responsable des retards dus à la force majeure ou aux décisions de l’assureur.</li>
        <li><strong>RGPD :</strong> Données traitées pour la gestion du dossier. Droits d’accès/rectification/opposition via {{ $cEmail ?: 'notre adresse e-mail' }}.</li>
        <li><strong>Droit & litiges :</strong> Droit français. Compétence des tribunaux du ressort du siège social de {{ $cName }}, sauf dispositions d’ordre public.</li>
      </ol>
    </div>

    {{-- Signature block --}}
    <div class="section">
      <div class="grid-2">
        <div class="col">
          <div class="sig-box">
            <div class="sig-label">Signature du client</div>
            <div class="sig-meta">
              Nom : <strong>{{ $clientName ?: '—' }}</strong><br>
              Fait à : ____________________ &nbsp; le : {{ $today }}<br>
              Lu et approuvé
            </div>
            {{-- Les “fields” e-sign sont ajoutés via l’API (pas besoin d’ancres). --}}
          </div>
        </div>
        <div class="col">
          <div class="sig-box">
            <div class="sig-label">Cachet & signature de {{ $cName }}</div>
            <div class="sig-meta">
              Représentant : ____________________<br>
              Fait à : ____________________ &nbsp; le : {{ $today }}
            </div>
          </div>
        </div>
      </div>

      <p class="small muted mt-12">
        En signant, le client confirme avoir pris connaissance et accepté les présentes conditions, autorise {{ $cName }} à gérer la relation avec l’assureur et,
        le cas échéant, cède sa créance d’indemnisation à {{ $cName }} à hauteur de la facture.
      </p>
    </div>
  </main>
</body>
</html>