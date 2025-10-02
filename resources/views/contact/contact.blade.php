@php
    // ---- Helpers (Dompdf likes absolute or base64 paths) ----
    function img64($absPath) {
        try {
            if (!$absPath || !file_exists($absPath)) return null;
            $mime = mime_content_type($absPath);
            $data = base64_encode(file_get_contents($absPath));
            return "data:$mime;base64,$data";
        } catch (\Throwable $e) { return null; }
    }

    // Company & Client fallbacks (adapt to your fields)
    $company  = $company ?? (object)[
        'name' => 'GS AUTO',
        'address' => '10 Impasse des Cormiers, 49460 Feneu',
        'phone' => '06 67 72 43 94',
        'email' => 'contact@gsauto.fr',
        'siret' => '—',
        'logo_path' => isset($company?->logo_path) ? storage_path('app/public/'.$company->logo_path) : null,
        'signature_path' => isset($company?->signature_path) ? storage_path('app/public/'.$company->signature_path) : null,
    ];

    $c = $client;
    // Try to collect what we can from your model
    $assurance  = $c->nom_assurance ?? '';
    $contrat    = $c->numero_police ?? '';
    $sinistreNo = $c->numero_sinistre ?? '';
    $dateDecl   = $c->date_declaration ? \Carbon\Carbon::parse($c->date_declaration)->format('d/m/Y') : now()->format('d/m/Y');
    $dateSin    = $c->date_sinistre ? \Carbon\Carbon::parse($c->date_sinistre)->format('d/m/Y') : '—';
    $vehicule   = trim(($c->marque ?? '').' '.($c->modele ?? ''));
    $immat      = $c->plaque ?? '';
    $km         = $c->kilometrage ? number_format((float)$c->kilometrage, 0, ',', ' ') : '—';

    // Optional signatures stored on client
    $sigClient  = !empty($c->signature_path) ? storage_path('app/public/'.$c->signature_path) : null;
    $sigCompany = $company->signature_path ?? null;

    $logo64     = img64($company->logo_path ?? null);
    $sigClient64  = img64($sigClient);
    $sigCompany64 = img64($sigCompany);

    // Today
    $today = now()->format('d/m/Y');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Pack Cession – {{ $c->prenom }} {{ $c->nom_assure }} – {{ $immat }}</title>
<style>
  @page { margin: 28mm 18mm 25mm 18mm; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
  h1,h2,h3 { margin: 0 0 8px; }
  h1 { font-size: 22px; color:#f59e0b; } /* amber-500 */
  h2 { font-size: 16px; }
  .muted { color:#666; font-size: 11px; }
  .tiny { font-size: 10px; color:#555; }
  .row { display:flex; gap:14px; }
  .col { flex:1; }
  .box { border:1px solid #ddd; border-radius:6px; padding:10px 12px; margin:10px 0; }
  .kv { display:flex; }
  .kv b { width:180px; display:inline-block; }
  .hr { height:1px; background:#e5e7eb; margin:12px 0; }
  .sign { height:70px; margin-top:6px; }
  .head { display:flex; align-items:center; justify-content:space-between; }
  .head .brand { display:flex; align-items:center; gap:10px; }
  .logo { height:40px; }
  .page-break { page-break-after: always; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #e5e7eb; padding:8px; }
  th { background:#f9fafb; text-align:left; }
  .center { text-align:center; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="head">
  <div class="brand">
    @if($logo64)<img class="logo" src="{{ $logo64 }}">@endif
    <div>
      <h1>GS AUTO</h1>
      <div class="tiny">{{ $company->address }} · Tél: {{ $company->phone }} · {{ $company->email }}</div>
      <div class="tiny">SIRET : {{ $company->siret }}</div>
    </div>
  </div>
  <div class="tiny" style="text-align:right">
    Généré le {{ $today }}<br>
    Dossier client : #{{ $c->id }}
  </div>
</div>

{{-- 1) Déclaration de bris de glace --}}
<h2 style="margin-top:14px;">Déclaration de bris de glace</h2>
<div class="box">
  <div class="row">
    <div class="col">
      <h3>Assuré</h3>
      <div class="kv"><b>Nom / Prénom</b> {{ $c->nom_assure }} {{ $c->prenom }}</div>
      <div class="kv"><b>Adresse</b> {{ $c->adresse }}</div>
      <div class="kv"><b>Téléphone</b> {{ $c->telephone }}</div>
      <div class="kv"><b>Email</b> {{ $c->email }}</div>
    </div>
    <div class="col">
      <h3>Véhicule</h3>
      <div class="kv"><b>Véhicule</b> {{ $vehicule ?: '—' }}</div>
      <div class="kv"><b>Immatriculation</b> {{ $immat }}</div>
      <div class="kv"><b>Kilométrage</b> {{ $km }} km</div>
      <div class="kv"><b>Type vitrage</b> {{ $c->type_vitrage ?? '—' }}</div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <h3>Assurance</h3>
      <div class="kv"><b>Compagnie</b> {{ $assurance }}</div>
      <div class="kv"><b>N° contrat</b> {{ $contrat }}</div>
      <div class="kv"><b>N° sinistre</b> {{ $sinistreNo ?: '—' }}</div>
    </div>
    <div class="col">
      <h3>Déclaration</h3>
      <div class="kv"><b>Date de déclaration</b> {{ $dateDecl }}</div>
      <div class="kv"><b>Date du sinistre</b> {{ $dateSin }}</div>
      <div class="kv"><b>Circonstances</b> {{ $c->raison ?? '—' }}</div>
      <div class="kv"><b>Adresse de pose</b> {{ $c->adresse_pose ?? '—' }}</div>
    </div>
  </div>
  <div class="hr"></div>
  <p class="tiny">
    Conformément à l’article L.211-5-1 du code des assurances et à l’arrêté du 29/12/2014, l’assuré exerce son libre choix du réparateur.
    Le vitrage endommagé sera remplacé en urgence par {{ $company->name }}.
  </p>
</div>

<div class="row">
  <div class="col">
    <b>Signature de l’assuré</b>
    @if($sigClient64)
      <div><img class="sign" src="{{ $sigClient64 }}"></div>
    @else
      <div class="sign" style="border-bottom:1px solid #aaa;"></div>
    @endif
  </div>
  <div class="col">
    <b>Cachet / Signature du garage</b>
    @if($sigCompany64)
      <div><img class="sign" src="{{ $sigCompany64 }}"></div>
    @else
      <div class="sign" style="border-bottom:1px solid #aaa;"></div>
    @endif
  </div>
</div>

<div class="page-break"></div>

{{-- 2) Notification de cession de créance --}}
<h2>Notification de cession de créance</h2>
<div class="box">
  <div class="kv"><b>Assurance</b> {{ $assurance }}</div>
  <div class="kv"><b>Contrat n°</b> {{ $contrat }}</div>
  <div class="kv"><b>Véhicule</b> {{ $vehicule ?: '—' }} · {{ $immat }}</div>
  <div class="kv"><b>Sinistre du</b> {{ $dateSin }}</div>
  <p style="margin-top:8px">
    Par la présente, l’assuré notifie à la compagnie d’assurance la cession de sa créance d’indemnisation au profit du réparateur
    {{ $company->name }}, et demande le règlement direct des réparations entre les mains de ce dernier, au titre du sinistre susvisé.
  </p>
</div>

<div class="row">
  <div class="col">
    <b>Fait le</b> {{ $today }}<br>
    <b>Signature de l’assuré</b>
    @if($sigClient64)
      <div><img class="sign" src="{{ $sigClient64 }}"></div>
    @else
      <div class="sign" style="border-bottom:1px solid #aaa;"></div>
    @endif
  </div>
  <div class="col">
    <b>Pour le réparateur</b> (acceptation)<br>
    @if($sigCompany64)
      <div><img class="sign" src="{{ $sigCompany64 }}"></div>
    @else
      <div class="sign" style="border-bottom:1px solid #aaa;"></div>
    @endif
  </div>
</div>

<div class="page-break"></div>

{{-- 3) Convention de cession de créance --}}
<h2>Convention de cession de créance</h2>
<div class="box">
  <p>
    L’assuré cède à {{ $company->name }} l’intégralité de ses droits à indemnisation relatifs au sinistre du {{ $dateSin }},
    concernant le véhicule {{ $vehicule ?: '—' }} ({{ $immat }}), couvert par la police n° {{ $contrat }} ({{ $assurance }}).
  </p>
  <ul class="tiny" style="margin-left:16px">
    <li>Le réparateur effectue les réparations nécessaires conformément aux règles de l’art et à l’ordre de réparation.</li>
    <li>Le client reste débiteur du solde éventuel non couvert par l’assurance (ex : franchise, exclusions), exigible à facturation.</li>
    <li>En cas de paiement reçu par erreur de l’assurance, le client s’engage à le reverser au réparateur sans délai (C. civ. 1302-1).</li>
  </ul>
</div>

<div class="row">
  <div class="col">
    <b>Signature de l’assuré<br><span class="tiny">« Lu et approuvé, bon pour cession de créance »</span></b>
    @if($sigClient64)
      <div><img class="sign" src="{{ $sigClient64 }}"></div>
    @else
      <div class="sign" style="border-bottom:1px solid #aaa;"></div>
    @endif
  </div>
  <div class="col">
    <b>Signature du réparateur<br><span class="tiny">« Bon pour acceptation »</span></b>
    @if($sigCompany64)
      <div><img class="sign" src="{{ $sigCompany64 }}"></div>
    @else
      <div class="sign" style="border-bottom:1px solid #aaa;"></div>
    @endif
  </div>
</div>

<div class="page-break"></div>

{{-- 4) Ordre de réparation (OR) --}}
<h2>Ordre de réparation</h2>
<div class="box">
  <div class="kv"><b>Lieu / Date</b> {{ $company->name }}, le {{ $today }}</div>
  <div class="kv"><b>Client</b> {{ $c->nom_assure }} {{ $c->prenom }} — {{ $c->telephone }}</div>
  <div class="kv"><b>Véhicule</b> {{ $vehicule ?: '—' }} · {{ $immat }}</div>
  <div class="kv"><b>Kilométrage</b> {{ $km }} km</div>
</div>

<table>
  <thead>
    <tr>
      <th>Description</th>
      <th class="center" style="width:90px">Qté</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Pare-brise</td><td class="center">1.0</td></tr>
    <tr><td>Remplacement pare-brise (MO)</td><td class="center">1.0</td></tr>
    <tr><td>Kit agrafes/joints</td><td class="center">1.0</td></tr>
    <tr><td>Kit collage</td><td class="center">1.0</td></tr>
    <tr><td>Nettoyage bris de glace</td><td class="center">1.0</td></tr>
    <tr><td>Retraitement des déchets</td><td class="center">1.0</td></tr>
  </tbody>
</table>

<p class="tiny" style="margin-top:10px">
  Je soussigné(e) {{ $c->nom_assure }} {{ $c->prenom }}, donne l’ordre d’effectuer les travaux décrits ci-dessus et reconnais
  avoir pris connaissance des conditions générales.
</p>

<div class="row" style="margin-top:10px">
  <div class="col">
    <b>Signature du client</b>
    @if($sigClient64)
      <div><img class="sign" src="{{ $sigClient64 }}"></div>
    @else
      <div class="sign" style="border-bottom:1px solid #aaa;"></div>
    @endif
  </div>
  <div class="col">
    <b>Pour le réparateur</b>
    @if($sigCompany64)
      <div><img class="sign" src="{{ $sigCompany64 }}"></div>
    @else
      <div class="sign" style="border-bottom:1px solid #aaa;"></div>
    @endif
  </div>
</div>

</body>
</html>