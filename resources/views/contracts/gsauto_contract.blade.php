@php
    /** @var \App\Models\Client $client */
    $company = $client->company;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Contrat GS Auto – {{ $client->nomComplet }}</title>
<style>
    @page { margin: 28mm 18mm; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; font-size: 12px; }
    h1,h2,h3{ color:#0f172a; margin:0 0 8px; }
    h1{ font-size:20px; }
    h2{ font-size:16px; margin-top:18px; border-bottom:1px solid #ececec; padding-bottom:4px; }
    .header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .logo{ height:46px; }
    .box{ border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin-top:10px; }
    .muted{ color:#6b7280; }
    .row{ display:flex; gap:14px; }
    .col{ flex:1; }
    .kv{ margin:4px 0; }
    .kv label{ display:block; font-size:11px; color:#6b7280; }
    .kv div{ font-weight:600; }
    .tag{ display:inline-block; background:#ffedd5; color:#9a3412; padding:2px 8px; border-radius:999px; font-size:11px; }
    .section{ margin-top:16px; }
    .sign-block{ margin-top:18px; display:flex; gap:16px; }
    .sign{ flex:1; border:1px dashed #d1d5db; border-radius:8px; min-height:90px; padding:10px; }
    .note{ font-size:11px; color:#374151; margin-top:8px; }
    .orange{ color:#f97316; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
  <div>
    <h1 class="orange">Contrat & Cession de créance</h1>
    <div class="muted">Document contractuel à signer électroniquement (Yousign)</div>
  </div>
  <div>
    @if($company?->logo)
      <img class="logo" src="{{ public_path('storage/'.$company->logo) }}" alt="Logo société">
    @else
      <strong>{{ $company?->commercial_name ?? $company?->name ?? 'GS Auto' }}</strong>
    @endif
  </div>
</div>

{{-- SOCIETE --}}
<div class="box">
  <h2>Réparateur (Société)</h2>
  <div class="row">
    <div class="col kv">
      <label>Raison sociale</label>
      <div>{{ $company->commercial_name ?? $company->name }}</div>
    </div>
    <div class="col kv">
      <label>SIRET</label>
      <div>{{ $company->siret ?? '—' }}</div>
    </div>
    <div class="col kv">
      <label>Tél / Email</label>
      <div>{{ $company->phone ?? '—' }} / {{ $company->email ?? '—' }}</div>
    </div>
  </div>
  <div class="kv">
    <label>Adresse</label>
    <div>{{ $company->address }}, {{ $company->postal_code }} {{ $company->city }}</div>
  </div>
</div>

{{-- CLIENT / VEHICULE --}}
<div class="box">
  <h2>Assuré (Client final) & Véhicule</h2>
  <div class="row">
    <div class="col">
      <div class="kv">
        <label>Assuré</label>
        <div>{{ $client->nomComplet }}</div>
      </div>
      <div class="kv">
        <label>Coordonnées</label>
        <div>{{ $client->adresse }}, {{ $client->code_postal }} {{ $client->ville }}</div>
      </div>
      <div class="kv">
        <label>Contact</label>
        <div>{{ $client->telephone }} / {{ $client->email }}</div>
      </div>
      <div class="kv">
        <label>Références</label>
        <div>Interne: {{ $client->reference_interne ?? '—' }} – Client: {{ $client->reference_client ?? '—' }}</div>
      </div>
    </div>
    <div class="col">
      <div class="kv">
        <label>Immatriculation / Marque / Modèle</label>
        <div>{{ $client->plaque ?? '—' }} / {{ $client->marque ?? '—' }} / {{ $client->modele ?? '—' }} @if($client->ancien_modele_plaque) <span class="tag">Ancien modèle</span>@endif</div>
      </div>
      <div class="kv">
        <label>Type de vitrage</label>
        <div>{{ $client->type_vitrage ?? '—' }}</div>
      </div>
      <div class="kv">
        <label>Kilométrage</label>
        <div>{{ $client->kilometrage ? number_format($client->kilometrage, 0, ',', ' ') . ' km' : '—' }}</div>
      </div>
      <div class="kv">
        <label>Adresse de pose</label>
        <div>{{ $client->adresse_pose ?? '—' }}</div>
      </div>
    </div>
  </div>
</div>

{{-- ASSURANCE --}}
<div class="box">
  <h2>Assurance</h2>
  <div class="row">
    <div class="col kv">
      <label>Compagnie</label>
      <div>{{ $client->nom_assurance ?? '—' }}</div>
    </div>
    <div class="col kv">
      <label>N° Police</label>
      <div>{{ $client->numero_police ?? '—' }}</div>
    </div>
    <div class="col kv">
      <label>Référence sinistre</label>
      <div>{{ $client->numero_sinistre ?? '—' }}</div>
    </div>
  </div>
  <div class="row">
    <div class="col kv">
      <label>Date du sinistre</label>
      <div>{{ $client->date_sinistre ? \Carbon\Carbon::parse($client->date_sinistre)->format('d/m/Y') : '—' }}</div>
    </div>
    <div class="col kv">
      <label>Date de déclaration</label>
      <div>{{ $client->date_declaration ? \Carbon\Carbon::parse($client->date_declaration)->format('d/m/Y') : '—' }}</div>
    </div>
    <div class="col kv">
      <label>Autre assurance</label>
      <div>{{ $client->autre_assurance ?? '—' }}</div>
    </div>
  </div>
  <div class="kv">
    <label>Connu par</label>
    <div>{{ $client->connu_par ?? '—' }}</div>
  </div>
</div>

{{-- 1. DECLARATION & ORDRE DE REPARATION --}}
<div class="section">
  <h2>1. Déclaration et ordre de réparation</h2>
  <p>
    Je soussigné(e) <strong>{{ $client->nomComplet }}</strong>, autorise la société
    <strong>{{ $company->commercial_name ?? $company->name }}</strong> (« GS Auto ») à procéder aux réparations
    nécessaires sur mon véhicule, conformément aux règles de l’art et aux recommandations des constructeurs.
  </p>
  <p class="muted">
    Raison / circonstances : {{ $client->raison ?? '—' }}.
  </p>
</div>

{{-- 2. CESSION DE CREANCE --}}
<div class="section">
  <h2>2. Cession de créance (articles 1321 et s. du Code civil)</h2>
  <p>
    En contrepartie des réparations, je cède ce jour à <strong>{{ $company->commercial_name ?? $company->name }}</strong>
    l’intégralité de ma créance à l’encontre de mon assureur, résultant du sinistre susvisé.
    Cette cession vaut paiement direct de la part de l’assureur entre les mains du réparateur.
  </p>
  <p>
    J’atteste être à jour de mes primes d’assurance et garantis l’existence de la créance cédée.
    Tout solde non pris en charge par l’assureur (franchise, déchéance, exclusions…) restera à ma charge.
  </p>
</div>

{{-- 3. CONVENTION DE REGLEMENT --}}
<div class="section">
  <h2>3. Convention de règlement</h2>
  <p><strong>Article 1 – Remise du véhicule</strong> : Le client remet son véhicule pour la remise en état.</p>
  <p><strong>Article 2 – Engagements du réparateur</strong> : GS Auto réalise les réparations nécessaires et remplace les pièces défectueuses par des pièces neuves ou équivalentes.</p>
  <p><strong>Article 3 – Paiement</strong> : La cession de créance couvre la facture des réparations.
     En cas de refus ou de non-paiement total/partiel par l’assureur, le client réglera le solde à GS Auto.</p>
  <p><strong>Article 4 – Garantie du cédant</strong> : Le client garantit l’exactitude des informations communiquées et l’existence de la créance.</p>
  <p><strong>Article 5 – Clause pénale</strong> : En cas de mauvaise foi ou de fausse déclaration, la totalité des sommes dues sera exigible, majorées d’un taux de pénalité
    conventionnelle ({{ $company->penalty_rate ?? '15%' }}).</p>
</div>

{{-- MENTIONS LEGALES --}}
<div class="note">
  <strong>Libre choix du réparateur :</strong> L’assuré dispose du libre choix du réparateur (art. L.211-5-1 du Code des assurances).
</div>

{{-- SIGNATURES --}}
<div class="sign-block">
  <div class="sign">
    <div><strong>Signature du client</strong></div>
    <div class="muted">« Lu et approuvé – bon pour cession de créance »</div>
    <div class="muted">Nom : {{ $client->nomComplet }} – Email : {{ $client->email }}</div>
  </div>
  <div class="sign">
    <div><strong>Signature GS Auto ({{ $company->commercial_name ?? $company->name }})</strong></div>
    <div class="muted">SIRET : {{ $company->siret ?? '—' }} – {{ $company->email ?? '' }}</div>
  </div>
</div>

</body>
</html>