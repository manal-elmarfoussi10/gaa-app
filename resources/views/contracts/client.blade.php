<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Contrat GS Auto</title>
  <style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 12px; }
    h1 { font-size: 20px; margin: 0 0 10px; }
    .box { border:1px solid #333; padding:12px; margin-bottom:12px; }
    .row { display:flex; justify-content:space-between; }
  </style>
</head>
<body>
  <h1>Contrat GS Auto</h1>

  <div class="box">
    <strong>Client</strong><br>
    {{ $client->prenom }} {{ $client->nom_assure }}<br>
    Email: {{ $client->email }} · Tél: {{ $client->telephone }}<br>
    Adresse: {{ $client->adresse }}
  </div>

  <div class="box">
    <strong>Véhicule</strong><br>
    Immatriculation: {{ $client->plaque }}<br>
    Type de vitrage: {{ $client->type_vitrage ?? '-' }}
  </div>

  <div class="box">
    <strong>Assurance</strong><br>
    {{ $client->nom_assurance }} · Police: {{ $client->numero_police }} · Sinistre: {{ $client->numero_sinistre ?? '-' }}
  </div>

  <p>Date: {{ now()->format('d/m/Y') }}</p>

  <p style="margin-top:40px">Signature du client: ____________________________</p>
</body>
</html>