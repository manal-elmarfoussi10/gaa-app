{{-- resources/views/contracts/contract.blade.php --}}
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $co = $client->company; // Company (GS Auto)
    $fmt = fn($d) => $d ? Carbon::parse($d)->locale('fr_FR')->translatedFormat('d/m/Y') : '—';

    $now = Carbon::now()->locale('fr_FR')->translatedFormat('d/m/Y');
    $villeJour = ($co->city ?? ''); // ville d’émission
    $garageName = $co->commercial_name ?: ($co->name ?: 'GS AUTO');

    // Assurance & sinistre
    $assurance = $client->nom_assurance ?: ($client->autre_assurance ?: '—');
    $numPolice = $client->numero_police ?: '—';
    $dateSinistre = $fmt($client->date_sinistre);
    $dateDeclaration = $fmt($client->date_declaration);
    $natureSinistre = $client->raison ?: 'Bris de glace';
    $vitrage = $client->type_vitrage ?: 'pare-brise';

    // Véhicule
    $immat = $client->plaque ?: '—';
    $kilom = $client->kilometrage ? number_format((int) $client->kilometrage, 0, ',', ' ') : '—';

    // Client identity
    $clientNom = Str::upper(trim($client->nom_assure ?: ''));
    $clientPrenom = trim($client->prenom ?: '');
    $clientNomComplet = trim($clientPrenom.' '.$clientNom) ?: 'Client';
    $clientAdresse = trim(($client->adresse ?: '').' '.($client->code_postal ?: '').' '.($client->ville ?: ''));
    $clientTel = $client->telephone ?: '—';
    $clientEmail = $client->email ?: '—';

    // Company identity
    $coLogo = $co->logo ?? null;
    $coAdr = trim(($co->address ?: '').' '.($co->postal_code ?: '').' '.($co->city ?: ''));
    $coTel = $co->phone ?: '—';
    $coEmail = $co->email ?: '—';
    $coSiret = $co->siret ?: '—';
    $coApe = $co->ape ?: ($co->naf_code ?? '—');
    $coTVA = $co->tva ?: '—';
    $coRCS = ($co->rcs_number && $co->rcs_city) ? ($co->rcs_number.' '.$co->rcs_city) : ($co->rcs_number ?? '—');

    // Company logo -> build a data URI for PDF
    $logoSrc = null;
    if (!empty($coLogo)) {
        try {
            $abs = \Illuminate\Support\Facades\Storage::disk('public')->path($coLogo);
            if (is_file($abs)) {
                $mime = function_exists('mime_content_type') ? mime_content_type($abs) : 'image/png';
                $data = base64_encode(file_get_contents($abs));
                $logoSrc = "data:{$mime};base64,{$data}";
            }
        } catch (\Throwable $e) {}
    }

    // Signatures
    $garageSignature = $co->signature_path ?? null;
    $sigSrc = null;
    if (!empty($garageSignature)) {
        try {
            $abs = \Illuminate\Support\Facades\Storage::disk('public')->path($garageSignature);
            if (is_file($abs)) {
                $mime = function_exists('mime_content_type') ? mime_content_type($abs) : 'image/png';
                $data = base64_encode(file_get_contents($abs));
                $sigSrc = "data:{$mime};base64,{$data}";
            }
        } catch (\Throwable $e) {}
    }

    // Ordre de réparation (si vous avez une source réelle, remplacez cette liste)
    $items = [
        ['desc' => Str::upper($vitrage), 'qt' => 1],
        ['desc' => 'REMPLACEMENT '.Str::upper($vitrage), 'qt' => 1],
        ['desc' => 'KIT AGRAFES ET/OU JOINTS', 'qt' => 1],
        ['desc' => 'KIT COLLAGE', 'qt' => 1],
        ['desc' => 'NETTOYAGE BRIS DE GLACE', 'qt' => 1],
        ['desc' => 'RETRAITEMENT DES DÉCHETS', 'qt' => 1],
    ];
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Contrat — Bris de glace ({{ $clientNomComplet }})</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    :root{
        --primary: #1F2937; /* Dark gray */
        --secondary: #6B7280; /* Medium gray */
        --accent: #F97316; /* Orange */
        --light-bg: #F9FAFB; /* Light background */
        --border: #E5E7EB; /* Border color */
        --white: #FFFFFF;
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--light-bg);color:var(--primary);font:14px/1.5 "Segoe UI",system-ui,-apple-system,sans-serif}
    .sheet{max-width:800px;margin:24px auto;background:var(--white);border:1px solid var(--border);border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.08);overflow:hidden}
    .bar{background:linear-gradient(90deg,var(--accent),#fed7aa);height:4px}
    .pad{padding:32px}
    .hdr{display:flex;gap:20px;align-items:center;justify-content:space-between;margin-bottom:12px;border-bottom:2px solid var(--light-bg);padding-bottom:16px}
    .co{display:flex;align-items:center;gap:16px}
    .co img{height:60px;width:auto;border-radius:8px;border:2px solid var(--border);background:var(--white)}
    h1,h2,h3{margin:0 0 10px}
    h1{font-size:24px;font-weight:700;letter-spacing:-.5px;color:var(--primary)}
    h2{font-size:18px;color:var(--accent);font-weight:600}
    h3{font-size:16px;color:var(--primary);margin-top:24px;font-weight:600}
    .muted{color:var(--secondary)}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
    .card{border:1px solid var(--border);border-radius:10px;padding:20px;background:var(--white);box-shadow:0 1px 3px rgba(0,0,0,.05)}
    .kv{display:grid;grid-template-columns:200px 1fr;gap:10px 15px;margin-top:8px}
    .kv div{padding:8px 0;border-bottom:1px solid var(--light-bg)}
    .kv div span{display:block;font-weight:500}
    .sec{margin-top:24px}
    .lead{font-size:14px;line-height:1.6}
    .tag{display:inline-block;padding:4px 12px;border-radius:20px;background:#fef3c7;color:var(--accent);font-weight:600;border:1px solid #fde68a}
    .signs{display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-top:24px}
    .sigbox{border:2px dashed var(--border);border-radius:12px;padding:16px;min-height:100px;display:flex;align-items:center;justify-content:center;text-align:center;background:var(--light-bg)}
    .sigimg{max-height:80px;max-width:100%;object-fit:contain}
    .foot{display:flex;gap:16px;align-items:center;color:var(--secondary);font-size:12px;margin-top:16px;border-top:1px solid var(--border);padding-top:12px}
    .pb{page-break-before:always}
    .actions{display:flex;justify-content:flex-end;gap:10px;padding:16px 20px;background:var(--white);border-top:1px solid var(--border)}
    .btn{background:var(--accent);color:var(--white);border:none;border-radius:8px;padding:10px 16px;font-weight:600;cursor:pointer;transition:background .2s}
    .btn:hover{background:#ea580c}
    .btn.out{background:var(--white);color:var(--accent);border:2px solid var(--accent)}
    table{width:100%;border-collapse:collapse;border-radius:8px;overflow:hidden;border:1px solid var(--border)}
    th,td{padding:12px;border-bottom:1px solid var(--border);font-size:14px}
    th{text-align:left;background:var(--light-bg);font-weight:600;color:var(--primary)}
    .t-right{text-align:right}
    .water{position:absolute;inset:auto auto 16px 16px;color:var(--accent);font-size:12px;font-weight:700}
    .wrap{position:relative}
    .legal-text{margin:16px 0;line-height:1.6;color:var(--primary)}
    .legal-text p{margin:0 0 12px}
    @media print{
        body{background:var(--white)}
        .sheet{box-shadow:none;border:0;margin:0;max-width:100%}
        .actions{display:none}
        .bar{height:3px}
        a{color:inherit;text-decoration:none}
    }
</style>
</head>
<body>

<div class="sheet">
    <div class="bar"></div>
    <div class="pad">
        <div class="hdr">
            <div class="co">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="Logo {{ $garageName }}">
                @else
                    <div style="height:48px;width:48px;border-radius:8px;border:1px solid var(--border);display:grid;place-items:center;font-weight:800;color:var(--brand)">GS</div>
                @endif
                <div>
                    <h1>{{ $garageName }}</h1>
                    <div class="muted">{{ $coAdr ?: 'Adresse non renseignée' }}</div>
                    <div class="muted">{{ $coEmail }} • {{ $coTel }}</div>
                </div>
            </div>
            <div style="text-align:right">
                <span class="tag">Dossier bris de glace</span>
                <div class="muted" style="margin-top:6px">Émis le {{ $now }}</div>
            </div>
        </div>

        {{-- Identités --}}
        <div class="grid">
            <div class="card">
                <h2>Assuré (Client)</h2>
                <div class="kv">
                    <div><span>Nom complet</span></div><div><strong>{{ $clientNomComplet }}</strong></div>
                    <div><span>Adresse</span></div><div>{{ $clientAdresse ?: '—' }}</div>
                    <div><span>Téléphone</span></div><div>{{ $clientTel }}</div>
                    <div><span>Email</span></div><div>{{ $clientEmail }}</div>
                </div>
            </div>
            <div class="card">
                <h2>Réparateur (Garage)</h2>
                <div class="kv">
                    <div><span>Raison sociale</span></div><div><strong>{{ $garageName }}</strong></div>
                    <div><span>Adresse</span></div><div>{{ $coAdr ?: '—' }}</div>
                    <div><span>SIRET / APE</span></div><div>{{ $coSiret }} / {{ $coApe }}</div>
                    <div><span>TVA / RCS</span></div><div>{{ $coTVA }} / {{ $coRCS }}</div>
                </div>
            </div>
        </div>

        {{-- Véhicule & Assurance --}}
        <div class="grid sec">
            <div class="card">
                <h2>Véhicule</h2>
                <div class="kv">
                    <div><span>Immatriculation</span></div><div><strong>{{ $immat }}</strong></div>
                    <div><span>Kilométrage</span></div><div>{{ $kilom }}</div>
                    <div><span>Vitrage concerné</span></div><div>{{ Str::title($vitrage) }}</div>
                </div>
            </div>
            <div class="card">
                <h2>Assurance</h2>
                <div class="kv">
                    <div><span>Compagnie</span></div><div><strong>{{ $assurance }}</strong></div>
                    <div><span>N° de police</span></div><div>{{ $numPolice }}</div>
                    <div><span>Date du sinistre</span></div><div>{{ $dateSinistre }}</div>
                    <div><span>Date de déclaration</span></div><div>{{ $dateDeclaration }}</div>
                </div>
            </div>
        </div>

        {{-- 1) DÉCLARATION DE BRIS DE GLACE --}}
        <div class="sec card">
            <h2>Déclaration de bris de glace</h2>
            <p class="lead">
                Je soussigné(e) <strong>{{ $clientNomComplet }}</strong> demeurant à <strong>{{ $clientAdresse ?: '—' }}</strong>,
                déclare que mon véhicule immatriculé <strong>{{ $immat }}</strong> a subi un <strong>{{ Str::lower($natureSinistre) }}</strong>
                le <strong>{{ $dateSinistre }}</strong>. Le vitrage concerné est <strong>{{ Str::lower($vitrage) }}</strong>.
            </p>
            <p class="muted" style="font-size:12px">
                Conformément au principe de libre choix (Code des assurances), je choisis le réparateur <strong>{{ $garageName }}</strong>.
                À l’issue de l’intervention, la facture sera adressée à mon assureur pour règlement direct entre ses mains.
            </p>
            <div class="signs">
                <div>
                    <h3>Signature de l’Assuré</h3>
                    <div class="sigbox">
                        <div>Signature manuscrite / électronique</div>
                    </div>
                    <div class="muted" style="margin-top:8px">{{ $villeJour ?: $co->city }}, le {{ $now }}</div>
                </div>
                <div>
                    <h3>Visa du Garage</h3>
                    <div class="sigbox">
                        @if($sigSrc)
                            <img class="sigimg" src="{{ $sigSrc }}" alt="Signature {{ $garageName }}">
                        @else
                            <div>Cachet & signature du garage</div>
                        @endif
                    </div>
                    <div class="muted" style="margin-top:8px">{{ $garageName }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2) NOTIFICATION + 3) CONVENTION DE CESSION DE CRÉANCE --}}
    <div class="pb"></div>
    <div class="bar"></div>
    <div class="pad">
        <div class="wrap">
            <h2>Notification de cession de créance & Convention</h2>
            <p class="lead">
                L’Assuré cède au Réparateur l’ensemble de ses droits à indemnisation relatifs au sinistre déclaré ci-dessus, en
                contrepartie des réparations effectuées sur le véhicule concerné, pour règlement direct par l’Assureur au profit du Garage.
            </p>

            <div class="grid">
                <div class="card">
                    <h3>Références de la créance</h3>
                    <div class="kv">
                        <div><span>Assureur</span></div><div><strong>{{ $assurance }}</strong></div>
                        <div><span>Police / Contrat</span></div><div>{{ $numPolice }}</div>
                        <div><span>Véhicule</span></div><div>Immat. <strong>{{ $immat }}</strong></div>
                        <div><span>Nature</span></div><div>{{ $natureSinistre }}</div>
                        <div><span>Date du sinistre</span></div><div>{{ $dateSinistre }}</div>
                    </div>
                </div>
                <div class="card">
                    <h3>Engagements</h3>
                    <ul style="margin:6px 0 0 18px;line-height:1.35">
                        <li>Le Garage effectue les réparations nécessaires selon les règles de l’art.</li>
                        <li>L’Assuré cède ses droits à indemnisation pour ce sinistre au Garage.</li>
                        <li>L’Assuré s’engage à restituer tout paiement reçu par erreur de l’Assureur.</li>
                        <li>La cession prend effet à la date de signature de la présente convention.</li>
                    </ul>
                </div>
            </div>

            <div class="signs">
                <div>
                    <h3>Signature de l’Assuré</h3>
                    <div class="sigbox">
                        <div>« Lu et approuvé, bon pour cession de créance »<br>Signature</div>
                    </div>
                    <div class="muted" style="margin-top:8px">{{ $villeJour ?: $co->city }}, le {{ $now }}</div>
                </div>
                <div>
                    <h3>Signature du Garage</h3>
                    <div class="sigbox">
                        @if($sigSrc)
                            <img class="sigimg" src="{{ $sigSrc }}" alt="Signature {{ $garageName }}">
                        @else
                            <div>« Bon pour acceptation »<br>Cachet & signature</div>
                        @endif
                    </div>
                    <div class="muted" style="margin-top:8px">{{ $garageName }}</div>
                </div>
            </div>

            <div class="foot">
                <span>Contact garage:</span>
                <span>{{ $coEmail }}</span> • <span>{{ $coTel }}</span> • <span>{{ $coAdr ?: '—' }}</span>
            </div>
            <div class="water">GS AUTO — Cession de créance</div>
        </div>
    </div>

    {{-- 4) ORDRE DE RÉPARATION --}}
    <div class="pb"></div>
    <div class="bar"></div>
    <div class="pad">
        <h2>Ordre de réparation</h2>
        <div class="grid">
            <div class="card">
                <h3>Client</h3>
                <div class="kv">
                    <div><span>Nom</span></div><div><strong>{{ $clientNomComplet }}</strong></div>
                    <div><span>Adresse</span></div><div>{{ $clientAdresse ?: '—' }}</div>
                    <div><span>Téléphone</span></div><div>{{ $clientTel }}</div>
                </div>
            </div>
            <div class="card">
                <h3>Véhicule</h3>
                <div class="kv">
                    <div><span>Immatriculation</span></div><div><strong>{{ $immat }}</strong></div>
                    <div><span>Kilométrage</span></div><div>{{ $kilom }}</div>
                    <div><span>Vitrage</span></div><div>{{ Str::title($vitrage) }}</div>
                </div>
            </div>
        </div>

        <div class="sec card">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="t-right">Quantité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $row)
                        <tr>
                            <td>{{ $row['desc'] }}</td>
                            <td class="t-right">{{ number_format($row['qt'], 1, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="muted" style="margin-top:10px;font-size:12px">
                Je soussigné(e) <strong>{{ $clientNomComplet }}</strong>, donne l’ordre d’effectuer les travaux décrits ci-dessus et reconnais avoir pris
                connaissance des conditions générales.
            </p>

            <div class="signs">
                <div>
                    <h3>Signature du Client</h3>
                    <div class="sigbox">Signature manuscrite / électronique</div>
                    <div class="muted" style="margin-top:8px">{{ $villeJour ?: $co->city }}, le {{ $now }}</div>
                </div>
                <div>
                    <h3>Visa du Garage</h3>
                    <div class="sigbox">
                        @if($sigSrc)
                            <img class="sigimg" src="{{ $sigSrc }}" alt="Signature {{ $garageName }}">
                        @else
                            Cachet & signature du garage
                        @endif
                    </div>
                    <div class="muted" style="margin-top:8px">{{ $garageName }}</div>
                </div>
            </div>
        </div>

        <div class="foot">
            <span>SIRET:</span> <span>{{ $coSiret }}</span> •
            <span>APE/NAF:</span> <span>{{ $coApe }}</span> •
            <span>TVA:</span> <span>{{ $coTVA }}</span> •
            <span>RCS:</span> <span>{{ $coRCS }}</span>
        </div>
    </div>

    <div class="actions">
        <button class="btn out" onclick="window.history.back()">Retour</button>
        <button class="btn" onclick="window.print()">Imprimer</button>
    </div>
</div>

</body>
</html>