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

  {{-- CLIENT / VÉHICULE --}}
  <div class="grid">
    <div class="card">
      <div class="section-title">Client</div>
      <table class="meta">
        <tr><th>Nom</th><td>{{ $fullName }}</td></tr>
        <tr><th>Email</th><td>{{ $client->email ?? '—' }}</td></tr>
        <tr><th>Téléphone</th><td>{{ $client->telephone ?? '—' }}</td></tr>
        <tr><th>Adresse</th><td>{{ $client->adresse ?? '—' }}</td></tr>
        <tr><th>Réf. Interne</th><td>{{ $client->reference_interne ?? '—' }}</td></tr>
        <tr><th>Réf. Client</th><td>{{ $client->reference_client ?? '—' }}</td></tr>
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

  {{-- ASSURANCE --}}
  <div class="card">
    <div class="section-title">Assurance</div>
    <table class="meta">
      <tr><th>Assureur</th><td>{{ $client->nom_assurance ?? '—' }}</td></tr>
      <tr><th>N° Police</th><td>{{ $client->numero_police ?? '—' }}</td></tr>
      <tr><th>N° Sinistre</th><td>{{ $client->numero_sinistre ?? '—' }}</td></tr>
      <tr><th>Autre assurance</th><td>{{ $client->autre_assurance ?? '—' }}</td></tr>
      <tr><th>Date du sinistre</th><td>{{ $client->date_sinistre ? \Carbon\Carbon::parse($client->date_sinistre)->format('d/m/Y') : '—' }}</td></tr>
      <tr><th>Date de déclaration</th><td>{{ $client->date_declaration ? \Carbon\Carbon::parse($client->date_declaration)->format('d/m/Y') : '—' }}</td></tr>
    </table>
  </div>

  {{-- Mandat / Cession --}}
  <div class="grid">
    <div class="block legal">
      <h2>Mandat</h2>
      <p>
        Le client mandate {{ $cName }} pour effectuer les démarches nécessaires auprès de l’assureur,
        gérer la relation sinistre et procéder, le cas échéant, au remplacement / à la réparation du vitrage.
      </p>
    </div>
    <div class="block legal">
      <h2>Cession de créance</h2>
      <p>
        En cas de prise en charge par l’assureur, le client cède à {{ $cName }} sa créance d’indemnisation
        à hauteur du montant de la facture émise par {{ $cName }} au titre de l’intervention réalisée.
      </p>
    </div>
  </div>

  {{-- Conditions --}}
  <div class="card legal">
    <h2>Conditions & Informations</h2>
    <p>• La prise en charge reste conditionnée à la garantie du contrat d’assurance et aux plafonds applicables.</p>
    <p>• Le reste à charge éventuel (franchise, exclusions, non-garanti) demeure dû par le client.</p>
    <p>• Le client confirme l’exactitude des informations communiquées et autorise leur transmission à l’assureur.</p>
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