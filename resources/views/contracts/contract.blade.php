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

        {{-- DÉCLARATION DE BRIS DE GLACE --}}
        <div class="sec">
            <div style="display:flex;justify-content:space-between;margin-bottom:20px">
                <div>
                    <div style="font-weight:700;font-size:16px">{{ $clientNomComplet }}</div>
                    <div>{{ $clientAdresse ?: '—' }}</div>
                    <div>Tél : {{ $clientTel }}</div>
                </div>
                <div style="text-align:right">
                    <div style="font-weight:700;font-size:16px">{{ $assurance }}</div>
                </div>
            </div>
            <h2 style="text-align:center;font-size:20px;margin:20px 0">DÉCLARATION DE BRIS DE GLACE</h2>
            <div style="margin-bottom:20px">
                <div><strong>Véhicule :</strong> {{ $client->marque ?: '—' }} {{ $client->modele ?: '—' }}</div>
                <div><strong>Immatriculation :</strong> {{ $immat }}</div>
                <div><strong>Contrat n°</strong>{{ $numPolice }}</div>
                <div><strong>Date de déclaration :</strong> {{ $dateDeclaration }}</div>
                <div><strong>Sinistre du</strong> {{ $dateSinistre }}</div>
            </div>
            <div class="legal-text">
                <p>Madame, Monsieur,</p>
                <p>Je soussigné {{ $clientNomComplet }} demeurant à : {{ $clientAdresse ?: '—' }}, déclare par la présente que conformément à l'Arrêté du 29 décembre 2014 relatif aux modalités d'information de l'assuré au moment du sinistre (et en cas de dommage garanti par mon contrat d'assurance), avoir la faculté de choisir le réparateur professionnel auquel je souhaite recourir et ce, comme indiqué dans l'article L.211-5-1 du code des assurances.</p>
                <p>Je déclare également que mon véhicule {{ $client->modele ?: '—' }}, immatriculé {{ $immat }} qui est assuré auprès de votre compagnie d'assurance par le contrat numéro : {{ $numPolice }} a subi un important bris de glace le {{ $dateSinistre }}, par suite d’une projection sur la route. Le vitrage concerné est : {{ Str::lower($vitrage) }}.</p>
                <p>Mon vitrage ayant été endommagé et m'empêchant d'avoir une bonne visibilité (dans le sens de l'article R316-1 et R316-3 du code de la route), Je suis dans l'obligation de le remplacer par un neuf en urgence chez mon Réparateur.</p>
                <p>Selon le principe de libre choix du consommateur et la loi du libre choix du réparateur (article L.211.5.1 du code des assurances), j’ai décidé d’effectuer ces travaux chez mon réparateur {{ $garageName }}.</p>
                <p>Une fois la prestation réalisée, mon réparateur vous adressera la facture de sa prestation, pour laquelle je vous prie de procéder au règlement de l’indemnité qui me revient, directement entre ses mains.</p>
                <p>Je vous prie d’agréer Madame, Monsieur, l'expression de mes salutations distinguées.</p>
            </div>
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

    {{-- 2) NOTIFICATION DE CESSION DE CREANCE --}}
    <div class="pb"></div>
    <div class="bar"></div>
    <div class="pad">
        <div class="wrap">
            <div style="display:flex;justify-content:space-between;margin-bottom:20px">
                <div>
                    <div style="font-weight:700;font-size:16px">{{ $clientNomComplet }}</div>
                    <div>{{ $clientAdresse ?: '—' }}</div>
                    <div>Tél : {{ $clientTel }}</div>
                    <div>(Ci-après désigné : l’Assuré)</div>
                </div>
                <div style="text-align:right">
                    <div style="font-weight:700;font-size:16px">{{ $garageName }}</div>
                    <div>{{ $coAdr ?: '—' }}</div>
                    <div>Tél : {{ $coTel }}</div>
                    <div>Garage : {{ $coEmail }}</div>
                    <div>Gestionnaire : relance@reseauglass.fr</div>
                    <div>(ci-après désigné « le Garage »)</div>
                </div>
            </div>
            <h2 style="text-align:center;font-size:20px;margin:20px 0">NOTIFICATION DE CESSION DE CREANCE</h2>
            <div style="text-align:center;margin-bottom:20px">RECOMMANDEE avec A/R</div>
            <div style="text-align:center;margin-bottom:20px">Le {{ $dateDeclaration }}</div>
            <div style="text-align:center;margin-bottom:20px">
                <div style="font-weight:700;font-size:16px">{{ $assurance }}</div>
                <div>(ci-après désignée « l’Assurance »)</div>
            </div>
            <div style="margin-bottom:20px">
                <div><strong>Véhicule :</strong> {{ $client->marque ?: '—' }} {{ $client->modele ?: '—' }}</div>
                <div><strong>Immatriculation :</strong> {{ $immat }}</div>
                <div><strong>Contrat n°</strong>{{ $numPolice }}</div>
                <div><strong>Date du sinistre :</strong> {{ $dateSinistre }}</div>
                <div><strong>Nature du sinistre :</strong> {{ $natureSinistre }}</div>
            </div>
            <div class="legal-text">
                <p>Madame, Monsieur,</p>
                <p>Je vous prie de trouver ci-joint une convention de cession de créance consentie par mes soins au garage {{ $garageName }} en application des dispositions de l'article 1324 du code civil.</p>
                <p>Conformément à l'ordonnance n°2016-131 du 10 février 2016, la présente lettre recommandée avec accusé de réception fait office de notification et suffit à faire respecter la convention de cession de créance jointe au verso et par laquelle je vous prie de bien vouloir procéder directement entre les mains de mon réparateur professionnel au paiement des réparations.</p>
                <p>N’ayant plus la faculté de recevoir de paiement de votre part, je vous prie de vouloir régler la somme directement auprès de mon réparateur désigné. Je lui ai accordé tous les pouvoirs nécessaires pour le recouvrement.</p>
                <p>Veuillez agréer, Madame, Monsieur, l’expression de mes salutations distinguées.</p>
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

    {{-- 3) CONVENTION DE CESSION DE CREANCE --}}
    <div class="pb"></div>
    <div class="bar"></div>
    <div class="pad">
        <div class="wrap">
            <h2 style="text-align:center;font-size:20px;margin:20px 0">CONVENTION DE CESSION DE CREANCE DE RÉPARATION</h2>
            <div style="margin-bottom:20px">
                <div><strong>RÉFÉRENCE CRÉANCE :</strong></div>
                <div><strong>Assurance :</strong> {{ $assurance }}</div>
                <div><strong>Véhicule :</strong> {{ $client->marque ?: '—' }} {{ $client->modele ?: '—' }}</div>
                <div><strong>Immatriculation :</strong> {{ $immat }}</div>
                <div><strong>Contrat n°</strong>{{ $numPolice }}</div>
                <div><strong>Date du sinistre :</strong> {{ $dateSinistre }}</div>
                <div><strong>Nature du sinistre :</strong> {{ $natureSinistre }}</div>
            </div>
            <div class="legal-text">
                <p><strong>Il a été décidé, entre l'Assuré et le Garage :</strong></p>
                <p><strong>Nature de la cession</strong><br>L'assuré a subi un sinistre dont les réparations sont couvertes par la police d'assurance n°{{ $numPolice }} émise par la compagnie d'assurance indiquée en marge. L'assuré assure détenir un droit à indemnisation au titre des garanties souscrites dans sa police d'assurance et s'engage par la présente convention à céder au garage l'ensemble de ses droits à indemnisation en contrepartie des réparations effectuées sur son véhicule.</p>
                <p><strong>Engagement des parties</strong><br>Le garage s'engage à effectuer toutes les réparations liées au sinistre et nécessaires à la remise en état du véhicule conformément à l'ordre de réparation établi. Le montant des travaux fixé par le réparateur conformément au barème constructeur, constitue la valeur pécuniaire de la créance.<br>L’assuré renonce à toute indemnisation de son assurance concernant le sinistre dont les références sont citées en marge. Comme nous précise l’article 1302-1 du code civil « Celui qui reçoit par erreur ou sciemment ce qui ne lui est pas dû doit le restituer à celui de qui il l'a indûment reçu ». Ainsi, l’assuré s’engage à restituer à son assurance tout paiement reçu par erreur de celle-ci. Il s’interdit également toute nouvelle cession de créance qui serait de ce fait nulle et sans effet.<br>L'assuré se reconnaît débiteur, sans exception ni réserve, du montant de la facture des réparations qui sera établie à l'issue des travaux et dont le paiement sera exigible immédiatement.<br>L'assuré garantie au garage de sa qualité de propriétaire du véhicule à réparer et de bénéficiaire du contrat d'assurance. En cas de situation particulière telle que la location ou un véhicule d'entreprise, il déclare formellement agir en tant que mandaté par le propriétaire du véhicule ou le bénéficiaire de l'indemnité d'assurance pour formaliser cette convention. Il assure également de la conformité personnelle ou au nom de son mandant vis-à-vis de sa compagnie d'assurance, incluant le paiement de toutes les primes, et atteste qu'aucun motif, de quelque nature que ce soit, ne remet en question son droit à indemnisation (y compris des causes de nullité telles que la compensation ou fausse déclaration). Le client déclare que sa compagnie d'assurance effectuera correctement le paiement de l'indemnité faisant l'objet de cette cession. En cas de besoin, le client autorise le garage à engager une procédure de recouvrement. En tout état de cause, le client reste responsable et garant de la véracité des informations fournies dans la cession de créance.</p>
                <p><strong>Acquittement intégral du montant des réparations</strong><br>En contrepartie des réparations effectuées, l'assuré cède au garage l'ensemble des droits et actions qu’il détient sur sa compagnie d’assurance au titre de l'indemnisation du sinistre garanti contractuellement par celle-ci et en application de l'article 1321 et suivants du code civil. Cette cession n’est valable que pour ce sinistre dont les références sont indiquées plus haut.</p>
                <p><strong>Conséquences de la cession de créance</strong><br>L’article 1323 du code civil indique que : « Entre les parties, le transfert de la créance, présente ou future, s'opère à la date de l'acte ». La créance est ainsi transférée automatiquement ce jour.</p>
                <p><strong>Clause de Médiation</strong><br>L’Assuré donne expressément mandat au Garage, en qualité de cessionnaire de la créance, pour entreprendre en son nom et pour son compte toutes démarches amiables, y compris la saisine de La Médiation de l’Assurance, en cas de litige avec l’assureur concernant le règlement de la présente créance. Le Garage est autorisé à transmettre au Médiateur l’ensemble des informations et pièces nécessaires et à recevoir toute correspondance relative à cette procédure. L’Assuré reconnaît être informé que la saisine du Médiateur est gratuite, qu’elle suspend la prescription pendant toute la durée de la médiation, et qu’il conserve la possibilité de mettre fin au mandat à tout moment par simple notification écrite au Garage.</p>
                <p>Le {{ $dateDeclaration }}</p>
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
            <div class="water">GS AUTO — Convention de cession</div>
        </div>
    </div>

    {{-- 4) ORDRE DE RÉPARATION --}}
    <div class="pb"></div>
    <div class="bar"></div>
    <div class="pad">
        <div style="text-align:center;margin-bottom:20px">
            <div style="font-weight:700;font-size:16px">{{ $garageName }}</div>
            <div>{{ $coAdr ?: '—' }}</div>
            <div>{{ $coEmail }}</div>
            <div>{{ $coTel }}</div>
        </div>
        <h2 style="text-align:center;font-size:20px;margin:20px 0">ORDRE DE RÉPARATION</h2>
        <div style="text-align:center;margin-bottom:20px">{{ $villeJour ?: $co->city }}, le {{ $dateDeclaration }}</div>
        <div style="margin-bottom:20px">
            <div style="font-weight:700;font-size:16px">{{ $clientNomComplet }}</div>
            <div>{{ $clientAdresse ?: '—' }}</div>
        </div>
        <div style="margin-bottom:20px">
            <h3>Véhicule</h3>
            <div><strong>Immatriculation :</strong> {{ $immat }} <strong>Marque :</strong> {{ $client->marque ?: '—' }}</div>
            <div><strong>Modèle :</strong> {{ $client->modele ?: '—' }} <strong>Kilométrage :</strong> {{ $kilom }}</div>
        </div>
        <div class="sec">
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
                Je soussigné(e), <strong>{{ $clientNomComplet }}</strong>, donne l'ordre d'effectuer les travaux décrits ci-dessus et reconnait avoir pris
                connaissance des conditions générales.
            </p>
            <div class="signs">
                <div>
                    <h3>Signature de l'Assuré</h3>
                    <div class="sigbox">
                        <div>Avec la mention « Lu et approuvé, bon pour cession de créance »<br>Signature</div>
                    </div>
                </div>
                <div>
                    <h3>Signature du Garage</h3>
                    <div class="sigbox">
                        @if($sigSrc)
                            <img class="sigimg" src="{{ $sigSrc }}" alt="Signature {{ $garageName }}">
                        @else
                            <div>Avec la mention « Bon pour acceptation »<br>Cachet & signature</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

  
</div>

</body>
</html>