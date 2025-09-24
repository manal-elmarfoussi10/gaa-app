<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Contrat GS Auto</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { font-size: 18px; margin-bottom: 10px; }
    .box { border:1px solid #ccc; padding:12px; border-radius:6px; }
  </style>
</head>
<body>
  <h1>Contrat de prestation – GS Auto</h1>
  <p>Date: {{ $today->format('d/m/Y') }}</p>

  <div class="box">
    <p><strong>Client:</strong> {{ $client->prenom }} {{ $client->nom_assure }}</p>
    <p><strong>Email:</strong> {{ $client->email }}</p>
    <p><strong>Téléphone:</strong> {{ $client->telephone }}</p>
    <p><strong>Immatriculation:</strong> {{ $client->plaque }}</p>
  </div>

  <p style="margin-top:16px;">
    Le client mandate GS Auto pour effectuer les prestations décrites dans le devis/facture associé.
  </p>

  <p style="margin-top:40px;">Signature du client:</p>
  <div style="border:1px dashed #999; height:80px;"></div>
</body>
</html>