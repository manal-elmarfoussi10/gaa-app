<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Contrat GS Auto</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { font-size: 20px; margin-bottom: 10px; }
    .box { border:1px solid #000; padding:12px; margin-top:20px; }
  </style>
</head>
<body>
  <h1>Contrat GS Auto</h1>
  <p>Date: {{ $today->format('d/m/Y') }}</p>

  <div class="box">
    <p><strong>Client:</strong> {{ $client->prenom }} {{ $client->nom_assure }}</p>
    <p><strong>Email:</strong> {{ $client->email }}</p>
    <p><strong>Téléphone:</strong> {{ $client->telephone }}</p>
    <p><strong>Immatriculation:</strong> {{ $client->plaque }}</p>
    @if($company)
      <p><strong>Entreprise:</strong> {{ $company->commercial_name ?? $company->name }}</p>
    @endif
  </div>

  <p style="margin-top:20px">
    En signant ce document, j’accepte les conditions de GS Auto.
  </p>
</body>
</html>