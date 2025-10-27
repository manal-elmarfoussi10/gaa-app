{{-- resources/views/contracts/contract.blade.php --}}
@php
  /** @var \App\Models\Client $client */
  $company = $company ?? $client->company;

  // Company fallbacks (legal info)
  $cName  = $company->commercial_name ?? $company->name ?? 'GS Auto';
  $cAddr  = trim(($company->address ? $company->address.', ' : '').($company->postal_code ?? '').' '.($company->city ?? ''));
  $cPhone = $company->phone ?? '';
  $cMail  = $company->email ?? '';
  $cSiret = $company->siret ?? '';
  $cTva   = $company->tva ?? '';

  // Company signature -> build a data URI (works in DOMPDF + others)
  use Illuminate\Support\Facades\Storage;

  $sigSrc = null;
  if (!empty($company?->signature_path)) {
      try {
          $abs = Storage::disk('public')->path($company->signature_path);
          if (is_file($abs)) {
              $mime = function_exists('mime_content_type') ? mime_content_type($abs) : 'image/png';
              $data = base64_encode(file_get_contents($abs));
              $sigSrc = "data:{$mime};base64,{$data}";
          }
      } catch (\Throwable $e) {
          $sigSrc = null; // fallback silently
      }
  }

  // Accent palette (subtle orange)
  $ORANGE      = '#F97316';
  $ORANGE_SOFT = '#FFF7ED';
  $ORANGE_LINE = '#FDBA74';
  $INK         = '#111827';

  $fullName = $client->nom_complet
           ?? trim(($client->prenom ?? '').' '.($client->nom_assure ?? $client->nom ?? ''));

  // Dynamic data for the contract content
  $clientAddress = trim(($client->adresse ?? '') . ' ' . ($client->code_postal ?? '') . ' ' . ($client->ville ?? ''));
  $clientPhone = $client->telephone ?? '';
  $assureur = $client->nom_assurance ?? 'Assureur';
  $immatriculation = $client->plaque ?? 'AW-053-JD';
  $vehicule = $client->marque_modele ?? 'RENAULT MASTER';
  $contratAssurance = $client->numero_police ?? 'F/375/147 512 012 F';
  $dateDeclaration = now()->format('d/m/Y');
  $dateSinistre = $client->date_sinistre ? \Carbon\Carbon::parse($client->date_sinistre)->format('d/m/Y') : now()->format('d/m/Y');
  $kilometrage = $client->kilometrage ? number_format($client->kilometrage, 0, ',', ' ') : '349709';
@endphp
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Contrat GS Auto – {{ $fullName }}</title>
  <style>
    @page { margin: 32px 36px; }
    body  { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: {{ $INK }}; font-size:12px; line-height:1.45; }

    .brand { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
    .brand__left  { display:flex; align-items:center; gap:14px; }
    .brand__name  { font-weight:800; font-size:18px; color:#1F2937; }
    .brand__meta  { font-size:10px; color:#4B5563; line-height:1.35; }
    .brand__tag   { font-weight:800; font-size:22px; color:#1F2937; }

    .logo {
      width:78px; height:78px; object-fit:contain; border-radius:10px; border:1px solid #E5E7EB; background:#FFFFFF;
    }

    .badge {
      display:inline-block; padding:4px 10px; border-radius:999px;
      background: {{ $ORANGE_SOFT }}; color: {{ $ORANGE }}; border:1px solid {{ $ORANGE_LINE }};
      font-weight:800; font-size:11px; text-transform:uppercase; letter-spacing:.02em;
    }

    h1 { font-size:20px; margin:0 0 8px 0; color:#0F172A; }
    h2 { font-size:14px; margin:0 0 8px 0; color:#0F172A; }
    .muted { color:#6B7280; }
    .small { font-size:10px; }

    .card { border:1px solid #E5E7EB; border-radius:10px; padding:12px 14px; margin-bottom:10px; }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap:10px; }

    table.meta { width:100%; border-collapse:separate; border-spacing:0; }
    table.meta th, table.meta td { padding:8px 10px; font-size:12px; vertical-align:top; border-bottom:1px solid #E5E7EB; }
    table.meta th { width:180px; color:#374151; font-weight:700; background:#F9FAFB; }

    .section-title {
      margin:14px 0 8px; font-size:13px; font-weight:900; color: {{ $ORANGE }};
      text-transform:uppercase; letter-spacing:.02em;
    }

    .block {
      border:1px dashed {{ $ORANGE_LINE }}; background: {{ $ORANGE_SOFT }};
      border-radius:10px; padding:12px 14px; margin-top:8px;
    }
    .legal p { margin:0 0 8px; text-align:justify; }

    .sign-grid { display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-top:12px; }
    .sign-box {
      border:2px dashed {{ $ORANGE_LINE }}; border-radius:12px; padding:14px 16px; min-height:140px; background:#FFFFFF;
    }
    .sign-box h3 { margin:0 0 6px; font-size:14px; color:#0F172A; }
    .sign-row { margin:4px 0; color:#1F2937; }
    .sign-line { display:inline-block; min-width:160px; border-bottom:2px solid {{ $ORANGE_LINE }}; transform: translateY(-3px); }
    .sign-hint { font-size:11px; color:#64748B; margin-top:8px; }

    .footer { margin-top:16px; padding-top:10px; border-top:2px solid {{ $ORANGE_LINE }}; font-size:10px; color:#6B7280; }
    .right { text-align:right; }

    /* Yousign smart anchors (hidden text; keep in DOM) */
    .y-anchor { font-size:1px; color:#ffffff; }
    .sig-img {
      display:block;
      max-width:180px;
      max-height:90px;
      object-fit:contain;
      margin-top:8px;
    }
    
  </style>
</head>
<body>

  {{-- Header / Branding --}}
  <div class="brand">
    <div class="brand__left">
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
    <div class="right">
      <br><br>
      <div class="badge">Contrat & Cession de créance</div><br>
      <div class="brand__tag">GS Auto</div>
    </div>
  </div>

  {{-- Sub meta --}}
  <div class="small muted" style="margin-bottom:10px;">
    Contrat n° {{ $client->id }} · Édité le {{ now()->format('d/m/Y') }}
  </div>

  {{-- DÉCLARATION DE BRIS DE GLACE --}}
  <div class="card">
    <h1>DÉCLARATION DE BRIS DE GLACE</h1>
    <p><strong>{{ $fullName }}</strong><br>
    {{ $clientAddress }}<br>
    Tél : {{ $clientPhone }}<br>
    {{ $assureur }}<br>
    DÉCLARATION DE BRIS DE GLACE<br>
    Véhicule : {{ $vehicule }}<br>
    Immatriculation : {{ $immatriculation }}<br>
    Contrat n°{{ $contratAssurance }}<br>
    Date de déclaration : {{ $dateDeclaration }}<br>
    Sinistre du {{ $dateSinistre }}</p>

    <p>Madame, Monsieur,</p>

    <p>Je soussigné {{ $fullName }} demeurant à : {{ $clientAddress }}, déclare par la présente que conformément à l'Arrêté du 29 décembre 2014 relatif aux modalités d'information de l'assuré au moment du sinistre (et en cas de dommage garanti par mon contrat d'assurance), avoir la faculté de choisir le réparateur professionnel auquel je souhaite recourir et ce, comme indiqué dans l'article L.211-5-1 du code des assurances.</p>

    <p>Je déclare également que mon véhicule {{ $vehicule }}, immatriculé {{ $immatriculation }} qui est assuré auprès de votre compagnie d'assurance par le contrat numéro : {{ $contratAssurance }} a subi un important bris de glace le {{ $dateSinistre }}, par suite d’une projection sur la route. Le vitrage concerné est : pare-brise.</p>

    <p>Mon vitrage ayant été endommagé et m'empêchant d'avoir une bonne visibilité (dans le sens de l'article R316-1 et R316-3 du code de la route), Je suis dans l'obligation de le remplacer par un neuf en urgence chez mon Réparateur.</p>

    <p>Selon le principe de libre choix du consommateur et la loi du libre choix du réparateur (article L.211.5.1 du code des assurances), j’ai décidé d’effectuer ces travaux chez mon réparateur {{ $cName }}.</p>

    <p>Une fois la prestation réalisée, mon réparateur vous adressera la facture de sa prestation, pour laquelle je vous prie de procéder au règlement de l’indemnité qui me revient, directement entre ses mains.</p>

    <p>Je vous prie d’agréer Madame, Monsieur, l'expression de mes salutations distinguées.</p>

    <p>Client: {{ $fullName }}</p>
    <p>Signature</p>

    <p>Je certifie avoir rempli cette déclaration en toute bonne foi</p>
    <p>{{ $fullName }}</p>
    <p>{{ $clientAddress }}</p>
    <p>Tél : {{ $clientPhone }}</p>
  </div>

  {{-- NOTIFICATION DE CESSION DE CREANCE --}}
  <div class="card">
    <h1>NOTIFICATION DE CESSION DE CREANCE</h1>
    <p>RECOMMANDEE avec A/R</p>
    <p>Le {{ $dateDeclaration }}</p>
    <p>{{ $assureur }}</p>
    <p>(ci-après désignée « l’Assurance »)</p>

    <p>Véhicule : {{ $vehicule }}</p>
    <p>Immatriculation : {{ $immatriculation }}</p>
    <p>Contrat n°{{ $contratAssurance }}</p>
    <p>Date du sinistre : {{ $dateSinistre }}</p>
    <p>Nature du sinistre : Bris de glace</p>

    <p>Madame, Monsieur,</p>

    <p>Je vous prie de trouver ci-joint une convention de cession de créance consentie par mes soins au garage {{ $cName }} en application des dispositions de l'article 1324 du code civil.</p>

    <p>Conformément à l'ordonnance n°2016-131 du 10 février 2016, la présente lettre recommandée avec accusé de réception fait office de notification et suffit à faire respecter la convention de cession de créance jointe au verso et par laquelle je vous prie de bien vouloir procéder directement entre les mains de mon réparateur professionnel au paiement des réparations.</p>

    <p>N’ayant plus la faculté de recevoir de paiement de votre part, je vous prie de vouloir régler la somme directement auprès de mon réparateur désigné. Je lui ai accordé tous les pouvoirs nécessaires pour le recouvrement.</p>

    <p>Veuillez agréer, Madame, Monsieur, l’expression de mes salutations distinguées.</p>

    <p>L'Assuré {{ $fullName }}</p>
    <p>{{ $clientAddress }}</p>
    <p>Tél : {{ $clientPhone }}</p>

    <p>Le Garage</p>
    <p>{{ $cName }}</p>
    <p>{{ $cAddr }}</p>
    <p>Tél : {{ $cPhone }}</p>
    <p>{{ $cMail }}</p>
    <p>Gestionnaire : {{ $cMail }}</p>
  </div>

  {{-- CONVENTION DE CESSION DE CREANCE --}}
  <div class="card">
    <h1>CONVENTION DE CESSION DE CREANCE DE RÉPARATION</h1>

    <p>Il a été décidé, entre l'Assuré et le Garage :</p>

    <h2>Nature de la cession</h2>
    <p>L'assuré a subi un sinistre dont les réparations sont couvertes par la police d'assurance n°{{ $contratAssurance }} émise par la compagnie d'assurance indiquée en marge. L'assuré assure détenir un droit à indemnisation au titre des garanties souscrites dans sa police d'assurance et s'engage par la présente convention à céder au garage l'ensemble de ses droits à indemnisation en contrepartie des réparations effectuées sur son véhicule.</p>

    <h2>Engagement des parties</h2>
    <p>Le garage s'engage à effectuer toutes les réparations liées au sinistre et nécessaires à la remise en état du véhicule conformément à l'ordre de réparation établi. Le montant des travaux fixé par le réparateur conformément au barème constructeur, constitue la valeur pécuniaire de la créance.</p>

    <p>L’assuré renonce à toute indemnisation de son assurance concernant le sinistre dont les références sont citées en marge. Comme nous précise l’article 1302-1 du code civil « Celui qui reçoit par erreur ou sciemment ce qui ne lui est pas dû doit le restituer à celui de qui il l'a indûment reçu ». Ainsi, l’assuré s’engage à restituer à son assurance tout paiement reçu par erreur de celle-ci. Il s’interdit également toute nouvelle cession de créance qui serait de ce fait nulle et sans effet.</p>

    <p>L'assuré se reconnaît débiteur, sans exception ni réserve, du montant de la facture des réparations qui sera établie à l'issue des travaux et dont le paiement sera exigible immédiatement.</p>

    <p>L'assuré garantie au garage de sa qualité de propriétaire du véhicule à réparer et de bénéficiaire du contrat d'assurance. En cas de situation particulière telle que la location ou un véhicule d'entreprise, il déclare formellement agir en tant que mandaté par le propriétaire du véhicule ou le bénéficiaire de l'indemnité d'assurance pour formaliser cette convention. Il assure également de la conformité personnelle ou au nom de son mandant vis-à-vis de sa compagnie d'assurance, incluant le paiement de toutes les primes, et atteste qu'aucun motif, de quelque nature que ce soit, ne remet en question son droit à indemnisation (y compris des causes de nullité telles que la compensation ou fausse déclaration). Le client déclare que sa compagnie d'assurance effectuera correctement le paiement de l'indemnité faisant l'objet de cette cession. En cas de besoin, le client autorise le garage à engager une procédure de recouvrement. En tout état de cause, le client reste responsable et garant de la véracité des informations fournues dans la cession de créance.</p>

    <h2>Acquittement intégral du montant des réparations</h2>
    <p>En contrepartie des réparations effectuées, l'assuré cède au garage l'ensemble des droits et actions qu’il détient sur sa compagnie d’assurance au titre de l'indemnisation du sinistre garanti contractuellement par celle-ci et en application de l'article 1321 et suivants du code civil. Cette cession n’est valable que pour ce sinistre dont les références sont indiquées plus haut.</p>

    <h2>Conséquences de la cession de créance</h2>
    <p>L’article 1323 du code civil indique que : « Entre les parties, le transfert de la créance, présente ou future, s'opère à la date de l'acte ». La créance est ainsi transférée automatiquement ce jour.</p>

    <h2>Clause de Médiation</h2>
    <p>L’Assuré donne expressément mandat au Garage, en qualité de cessionnaire de la créance, pour entreprendre en son nom et pour son compte toutes démarches amiables, y compris la saisine de La Médiation de l’Assurance, en cas de litige avec l’assureur concernant le règlement de la présente créance. Le Garage est autorisé à transmettre au Médiateur l’ensemble des informations et pièces nécessaires et à recevoir toute correspondance relative à cette procédure. L’Assuré reconnaît être informé que la saisine du Médiateur est gratuite, qu’elle suspend la prescription pendant toute la durée de la médiation, et qu’il conserve la possibilité de mettre fin au mandat à tout moment par simple notification écrite au Garage.</p>

    <p>Le {{ $dateDeclaration }}</p>

    <p>Signature de l'Assuré</p>
    <p>Avec la mention « Lu et approuvé, bon pour cession de créance »</p>

    <p>Signature du Garage</p>
    <p>Avec la mention « Bon pour acceptation »</p>

    <p>{{ $cName }}</p>
    <p>{{ $cAddr }}</p>
    <p>{{ $cMail }}</p>
    <p>Siret : {{ $cSiret }}</p>
  </div>

  {{-- ORDRE DE RÉPARATION --}}
  <div class="card">
    <h1>ORDRE DE RÉPARATION</h1>
    <p>{{ $cAddr }}, le {{ $dateDeclaration }}</p>

    <p>{{ $fullName }}</p>
    <p>{{ $clientAddress }}</p>

    <h2>Véhicule</h2>
    <p>Immatriculation : {{ $immatriculation }} Marque : {{ explode(' ', $vehicule)[0] }}</p>
    <p>Modèle : {{ implode(' ', array_slice(explode(' ', $vehicule), 1)) }} Kilométrage : {{ $kilometrage }}</p>

    <h2>Description Quantité</h2>
    <p>PARE-BRISE 1,0</p>
    <p>REMPLACEMENT PARE-BRISE</p>
    <p>MO-MECA T2 1,0</p>
    <p>KIT AGRAFES ET/OU JOINTS 1,0</p>
    <p>KIT COLLAGE 1,0</p>
    <p>NETTOYAGE BRIS DE GLACE 1,0</p>
    <p>RETRAITEMENT DES DECHETS 1,0</p>

    <p>Je soussigné(e), {{ $fullName }}, donne l'ordre d'effectuer les travaux décrits ci-dessus et reconnait avoir pris connaissance des conditions générales.</p>

    <p>{{ $cName }}</p>
    <p>N° Siret : {{ $cSiret }}</p>
    <p>Code APE : {{ $company->ape ?? '' }}</p>
    <p>TVA intracommunautaire : FR{{ $cTva }}</p>
  </div>

  {{-- Signatures --}}
  <div class="sign-grid">
    <div class="sign-box">
      <h3>Signature du client</h3>
      <div class="sign-row">Nom : <strong>{{ $fullName }}</strong></div>
      <div class="sign-row">Fait le : {{ now()->format('d/m/Y') }}</div>
      <div class="sign-hint">Lu et approuvé</div>
    </div>

    <div class="sign-box">
      <h3>Cachet & signature de {{ $cName }}</h3>
      <div class="sign-row">Fait le : {{ now()->format('d/m/Y') }}</div>
      @if($sigSrc)
        <img src="{{ $sigSrc }}" alt="Signature {{ $cName }}" class="sig-img">
      @else
        <div class="sign-hint">Signature non fournie</div>
      @endif
    </div>
  </div>


  <div class="footer">
    Document généré automatiquement – {{ $cName }} · {{ $cAddr }}
    @if($cPhone) · {{ $cPhone }} @endif
    @if($cMail) · {{ $cMail }} @endif
  </div>

</body>
</html>