<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\User;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Avoir;
use App\Models\Paiement;
use App\Models\Company;
use App\Models\Rdv;
use App\Models\Expense;
use App\Models\BonDeCommande;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Poseur;
use App\Models\Stock;

class FilesController extends Controller
{
    /** Base query; support roles bypass global scopes. */
    private function q(string $modelClass)
    {
        $user = auth()->user();
        $isSupport = $user && in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_CLIENT_SERVICE], true);
        return $isSupport ? $modelClass::query()->withoutGlobalScopes() : $modelClass::query();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_CLIENT_SERVICE], true), 403);

        $type = $request->input('type', 'clients');

        $companies = $this->q(Company::class)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        [$columns, $results] = $this->buildQuery($type, $request, true);

        return view('superadmin.files.index', [
            'filters'   => [
                'type'       => $type,
                'company_id' => $request->input('company_id'),
                'from'       => $this->firstNonNull($request, ['from','date_from','du']),
                'to'         => $this->firstNonNull($request, ['to','date_to','au']),
                'q'          => $request->input('q'),
            ],
            'columns'   => $columns,
            'results'   => $results,
            'companies' => $companies,
        ]);
    }

    public function export(Request $request)
    {
        abort_unless(auth()->user() && auth()->user()->role === User::ROLE_SUPERADMIN, 403);

        $type = $request->input('type', 'clients');
        [$columns, $rows] = $this->buildQuery($type, $request, false);

        $filename = 'export_'.$type.'_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($columns, $rows, $type) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($out, array_values($columns), ';');   // header

            foreach ($rows as $row) {
                $line = [];
                foreach (array_keys($columns) as $key) {
                    $line[] = self::renderCell($type, $key, $row, true);
                }
                fputcsv($out, $line, ';');
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    /** Build columns + dataset (with filters). */
    private function buildQuery(string $type, Request $request, bool $paginate): array
    {
        $dateFromRaw = $this->firstNonNull($request, ['from','date_from','du']);
        $dateToRaw   = $this->firstNonNull($request, ['to','date_to','au']);
        [$dateFrom, $dateTo] = $this->parseDateRange($dateFromRaw, $dateToRaw);

        $companyId  = $request->input('company_id');
        $q          = trim((string) $request->input('q', ''));

        $productNameCol = Schema::hasColumn('produits','designation') ? 'designation'
                         : (Schema::hasColumn('produits','name') ? 'name'
                         : (Schema::hasColumn('produits','nom') ? 'nom' : null));

        switch ($type) {
            // ================= DEVIS =================
            case 'devis': {
                $columns = [
                    'created_at' => 'Date',
                    'client'     => 'Client',
                    'titre'      => 'Titre',
                    'total_ht'   => 'Total HT',
                    'total_ttc'  => 'Total TTC',
                ];

                $query = $this->q(Devis::class)
                    ->with(['client' => function($q){
                        $q->withoutGlobalScopes()
                          ->select('id','nom_assure','prenom','email','telephone','company_id');
                    }])
                    ->select('*')
                    ->latest('created_at');

                if ($companyId) {
                    $query->whereHas('client', function(Builder $c) use ($companyId) {
                        $c->withoutGlobalScopes()->where('company_id', $companyId);
                    });
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'created_at');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('titre','like',"%$q%")
                          ->orWhere('prospect_name','like',"%$q%")
                          ->orWhereHas('client', function(Builder $c) use ($q) {
                              $c->withoutGlobalScopes()
                                ->where('nom_assure','like',"%$q%")
                                ->orWhere('prenom','like',"%$q%")
                                ->orWhere('email','like',"%$q%")
                                ->orWhere('telephone','like',"%$q%");
                          });
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================= FACTURES ==============
            case 'factures': {
                $columns = [
                    'date_facture' => 'Date',
                    'client'       => 'Client',
                    'total_ht'     => 'Total HT',
                    'total_ttc'    => 'Total TTC',
                    'is_paid'      => 'Payée',
                ];

                $query = $this->q(Facture::class)
                    ->with(['client' => function($q){
                        $q->withoutGlobalScopes()
                          ->select('id','nom_assure','prenom','email','telephone','company_id');
                    }])
                    ->select('*')
                    ->latest('date_facture');

                if ($companyId) {
                    $query->whereHas('client', function(Builder $c) use ($companyId) {
                        $c->withoutGlobalScopes()->where('company_id', $companyId);
                    });
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'date_facture');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('titre','like',"%$q%")
                          ->orWhere('prospect_name','like',"%$q%")
                          ->orWhereHas('client', function(Builder $c) use ($q) {
                              $c->withoutGlobalScopes()
                                ->where('nom_assure','like',"%$q%")
                                ->orWhere('prenom','like',"%$q%")
                                ->orWhere('email','like',"%$q%")
                                ->orWhere('telephone','like',"%$q%");
                          });
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================= AVOIRS =================
            case 'avoirs': {
                $columns = [
                    'created_at' => 'Date',
                    'client'     => 'Client',
                    'facture'    => 'Facture',
                    'montant'    => 'Montant',
                ];

                $query = $this->q(Avoir::class)
                    ->with([
                        'facture' => fn($f) => $f->select('*'),
                        'facture.client' => function($q){
                            $q->withoutGlobalScopes()
                              ->select('id','nom_assure','prenom','email','telephone','company_id');
                        }
                    ])
                    ->latest('created_at');

                if ($companyId) {
                    $query->whereHas('facture.client', function(Builder $c) use ($companyId) {
                        $c->withoutGlobalScopes()->where('company_id', $companyId);
                    });
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'created_at');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('montant','like',"%$q%")
                          ->orWhereHas('facture', function(Builder $f) use ($q) {
                              $f->where('titre','like',"%$q%")
                                ->orWhere('numero','like',"%$q%");
                          })
                          ->orWhereHas('facture.client', function(Builder $c) use ($q) {
                              $c->withoutGlobalScopes()
                                ->where('nom_assure','like',"%$q%")
                                ->orWhere('prenom','like',"%$q%")
                                ->orWhere('email','like',"%$q%")
                                ->orWhere('telephone','like',"%$q%");
                          });
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================= PAIEMENTS =============
            case 'paiements': {
                $columns = [
                    'date_paiement' => 'Date',
                    'client'        => 'Client',
                    'facture'       => 'Facture',
                    'montant'       => 'Montant',
                ];

                $dateCol  = Schema::hasColumn('paiements','date') ? 'date'
                           : (Schema::hasColumn('paiements','date_paiement') ? 'date_paiement' : 'created_at');

                $query = $this->q(Paiement::class)
                    ->with([
                        'facture' => fn($f) => $f->select('*'),
                        'facture.client' => function($q){
                            $q->withoutGlobalScopes()
                              ->select('id','nom_assure','prenom','email','telephone','company_id');
                        }
                    ])
                    ->orderBy($dateCol, 'desc');

                if ($companyId) {
                    $query->whereHas('facture.client', function(Builder $c) use ($companyId) {
                        $c->withoutGlobalScopes()->where('company_id', $companyId);
                    });
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, $dateCol);

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('methode','like',"%$q%")
                          ->orWhere('methode_paiement','like',"%$q%")
                          ->orWhere('montant','like',"%$q%")
                          ->orWhereHas('facture', function(Builder $f) use ($q) {
                              $f->where('titre','like',"%$q%")
                                ->orWhere('numero','like',"%$q%")
                                ->orWhere('prospect_name','like',"%$q%");
                          })
                          ->orWhereHas('facture.client', function(Builder $c) use ($q) {
                              $c->withoutGlobalScopes()
                                ->where('nom_assure','like',"%$q%")
                                ->orWhere('prenom','like',"%$q%")
                                ->orWhere('email','like',"%$q%")
                                ->orWhere('telephone','like',"%$q%");
                          });
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================= RDV ====================
            case 'rdv':
            case 'rdvs': {
                $columns = [
                    'start_time' => 'Début',
                    'end_time'   => 'Fin',
                    'client'     => 'Client',
                    'poseur'     => 'Poseur',
                    'status'     => 'Statut',
                ];

                $query = $this->q(Rdv::class)
                    ->with([
                        'client' => fn($q) => $q->withoutGlobalScopes()->select('id','nom_assure','prenom','email','telephone','company_id'),
                        'poseur' => fn($q) => $q->withoutGlobalScopes()->select('id','nom','email','telephone'),
                    ])
                    ->orderBy('start_time','desc');

                if ($companyId) {
                    if (Schema::hasColumn('rdvs','company_id')) {
                        $query->where('company_id',$companyId);
                    } elseif (Schema::hasColumn('rdvs','client_id') && Schema::hasColumn('clients','company_id')) {
                        $query->whereHas('client', fn(Builder $c)=>$c->withoutGlobalScopes()->where('company_id',$companyId));
                    }
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'start_time');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('title','like',"%$q%")
                          ->orWhere('status','like',"%$q%")
                          ->orWhereHas('client', fn(Builder $c)=>$c->withoutGlobalScopes()
                              ->where('nom_assure','like',"%$q%")
                              ->orWhere('prenom','like',"%$q%")
                              ->orWhere('email','like',"%$q%")
                              ->orWhere('telephone','like',"%$q%"))
                          ->orWhereHas('poseur', fn(Builder $p)=>$p->withoutGlobalScopes()
                              ->where('nom','like',"%$q%")
                              ->orWhere('email','like',"%$q%")
                              ->orWhere('telephone','like',"%$q%"));
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ============== DEPENSES ==================
            case 'depenses':
            case 'expenses': {
                $columns = [
                    'date'          => 'Date',
                    'client'        => 'Client',
                    'fournisseur'   => 'Fournisseur',
                    'paid_status'   => 'Statut',
                    'ht_amount'     => 'HT',
                    'ttc_amount'    => 'TTC',
                ];

                $query = $this->q(Expense::class)
                    ->with([
                        'client'      => fn($q)=>$q->withoutGlobalScopes()->select('id','nom_assure','prenom','email','telephone','company_id'),
                        'fournisseur' => fn($q)=>$q->withoutGlobalScopes()->select('id','nom_societe','company_id'),
                    ])
                    ->orderBy('date','desc');

                if ($companyId) {
                    if (Schema::hasColumn('expenses','company_id')) {
                        $query->where('company_id',$companyId);
                    } else {
                        $query->where(function(Builder $w) use ($companyId) {
                            $w->whereHas('client', fn(Builder $c)=>$c->withoutGlobalScopes()->where('company_id',$companyId))
                              ->orWhereHas('fournisseur', fn(Builder $f)=>$f->withoutGlobalScopes()->where('company_id',$companyId));
                        });
                    }
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'date');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('paid_status','like',"%$q%")
                          ->orWhere('description','like',"%$q%")
                          ->orWhereHas('client', fn(Builder $c)=>$c->withoutGlobalScopes()
                              ->where('nom_assure','like',"%$q%")
                              ->orWhere('prenom','like',"%$q%")
                              ->orWhere('email','like',"%$q%")
                              ->orWhere('telephone','like',"%$q%"))
                          ->orWhereHas('fournisseur', fn(Builder $f)=>$f->withoutGlobalScopes()
                              ->where('nom_societe','like',"%$q%"));
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ============ BONS DE COMMANDE ============
            case 'bons':
            case 'bons_de_commande':
            case 'purchase_orders': {
                $columns = [
                    'date'          => 'Date',
                    'fournisseur'   => 'Fournisseur',
                    'total_ht'      => 'Total HT',
                    'total_ttc'     => 'Total TTC',
                ];

                $dateCol = Schema::hasColumn('bon_de_commandes','date') ? 'date' : 'created_at';

                $query = $this->q(BonDeCommande::class)
                    ->select('*') // keep numero
                    ->with(['fournisseur' => fn($q)=>$q->withoutGlobalScopes()->select('id','nom_societe','company_id')])
                    ->orderBy($dateCol,'desc');

                if ($companyId && Schema::hasColumn('bon_de_commandes','company_id')) {
                    $query->where('company_id',$companyId);
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, $dateCol);

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('numero','like',"%$q%")
                          ->orWhere('status','like',"%$q%")
                          ->orWhereHas('fournisseur', fn(Builder $f)=>$f->withoutGlobalScopes()->where('nom_societe','like',"%$q%"));
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================ FOURNISSEURS ============
            case 'fournisseurs': {
                $columns = [
                    'created_at' => 'Date',
                    'nom_societe'=> 'Société',
                    'contact'    => 'Contact',
                ];

                $query = $this->q(Fournisseur::class)->orderBy('nom_societe');

                if ($companyId && Schema::hasColumn('fournisseurs','company_id')) {
                    $query->where('company_id',$companyId);
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'created_at');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('nom_societe','like',"%$q%")
                          ->orWhere('email','like',"%$q%")
                          ->orWhere('telephone','like',"%$q%");
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================= PRODUITS ===============
            case 'produits': {
                $columns = [
                    'created_at' => 'Date',
                    'designation'=> 'Produit',
                    'prix_ht'    => 'Prix HT',
                ];

                $query = $this->q(Produit::class)->latest('created_at');

                if ($companyId && Schema::hasColumn('produits','company_id')) {
                    $query->where('company_id',$companyId);
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'created_at');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q, $productNameCol) {
                        if ($productNameCol) $b->where($productNameCol,'like',"%$q%");
                        $b->orWhere('reference','like',"%$q%");
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================= POSEURS ================
            case 'poseurs': {
                $columns = [
                    'created_at' => 'Date',
                    'nom'        => 'Nom',
                    'contact'    => 'Contact',
                ];

                $query = $this->q(Poseur::class)->orderBy('nom');

                if ($companyId && Schema::hasColumn('poseurs','company_id')) {
                    $query->where('company_id',$companyId);
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'created_at');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('nom','like',"%$q%")
                          ->orWhere('email','like',"%$q%")
                          ->orWhere('telephone','like',"%$q%");
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================= STOCKS =================
            case 'stocks': {
                $columns = [
                    'created_at' => 'Date',
                    'produit'    => 'Produit',
                    'reference'  => 'Référence',
                    'quantity'   => 'Quantité',
                    'location'   => 'Emplacement',
                ];

                $productSelect = array_values(array_filter([
                    'id',
                    $productNameCol,
                    Schema::hasColumn('produits','reference') ? 'reference' : null,
                    Schema::hasColumn('produits','company_id') ? 'company_id' : null,
                ]));

                $query = $this->q(Stock::class)
                    ->with(['produit' => function($q) use ($productSelect) {
                        $q->withoutGlobalScopes();
                        if (!empty($productSelect)) $q->select($productSelect);
                    }])
                    ->latest('created_at');

                if ($companyId) {
                    if (Schema::hasColumn('stocks','company_id')) {
                        $query->where('company_id',$companyId);
                    } else {
                        $query->whereHas('produit', fn(Builder $p)=>$p->withoutGlobalScopes()->where('company_id',$companyId));
                    }
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'created_at');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q, $productNameCol) {
                        $b->where('location','like',"%$q%")
                          ->orWhereHas('produit', function(Builder $p) use ($q, $productNameCol) {
                              $p->withoutGlobalScopes();
                              if ($productNameCol) $p->where($productNameCol,'like',"%$q%");
                              $p->orWhere('reference','like',"%$q%");
                          });
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }

            // ================= CLIENTS ================
            case 'clients':
            default: {
                $columns = [
                    'created_at' => 'Date',
                    'client'     => 'Client',
                    'contact'    => 'Contact',
                    'vehicule'   => 'Véhicule',
                    'statut'     => 'Statut',
                ];

                $query = $this->q(Client::class)->latest('created_at');

                if ($companyId && Schema::hasColumn('clients','company_id')) {
                    $query->where('company_id', $companyId);
                }

                $this->applyDateRange($query, $dateFrom, $dateTo, 'created_at');

                if ($q !== '') {
                    $query->where(function(Builder $b) use ($q) {
                        $b->where('nom_assure','like',"%$q%")
                          ->orWhere('prenom','like',"%$q%")
                          ->orWhere('email','like',"%$q%")
                          ->orWhere('telephone','like',"%$q%")
                          ->orWhere('plaque','like',"%$q%")
                          ->orWhere('immatriculation','like',"%$q%");
                    });
                }

                $results = $paginate ? $query->paginate(25)->appends($request->query()) : $query->get();
                return [$columns, $results];
            }
        }
    }

    /** dd/mm/yyyy helpers */
    private function parseDateRange(?string $from, ?string $to): array
    {
        $f = $this->toCarbon($from, true);
        $t = $this->toCarbon($to, false);
        if ($f && !$t) $t = now()->endOfDay();
        if ($t && !$f) $f = Carbon::create(1970, 1, 1, 0, 0, 0);
        return [$f, $t];
    }
    private function toCarbon(?string $val, bool $start): ?Carbon
    {
        if (!$val) return null;
        $val = trim($val);
        $c = preg_match('~^\d{2}/\d{2}/\d{4}$~', $val)
            ? Carbon::createFromFormat('d/m/Y', $val)
            : Carbon::parse($val);
        return $start ? $c->startOfDay() : $c->endOfDay();
    }
    private function firstNonNull(Request $r, array $keys): ?string
    {
        foreach ($keys as $k) { $v = $r->input($k); if ($v !== null && $v !== '') return $v; }
        return null;
    }
    private function applyDateRange(Builder $query, ?Carbon $from, ?Carbon $to, string $column): void
    {
        if ($from) $query->where($column, '>=', $from);
        if ($to)   $query->where($column, '<=', $to);
    }

    /** Cell rendering (HTML/CSV). */
    public static function renderCell(string $type, string $key, $row, bool $plain = false)
    {
        $money = fn($v) => number_format((float)($v ?? 0), 2, ',', ' ').' €';

        // helper: client name with prospect fallback (for devis/facture)
        $clientNameFor = function($entity) {
            $client = $entity->client ?? null;
            $name = trim(($client->nom_assure ?? '').' '.($client->prenom ?? ''));
            if ($name !== '') return $name;
            // fallback to prospect name if defined
            return $entity->prospect_name ?? '';
        };

        switch ($type) {
            case 'devis':
                return match ($key) {
                    'created_at' => optional($row->created_at)->format('d/m/Y H:i'),
                    'client'     => $clientNameFor($row),
                    'titre'      => $row->titre ?? '',
                    'total_ht'   => $money($row->total_ht),
                    'total_ttc'  => $money($row->total_ttc),
                    default      => '',
                };

            case 'factures':
                return match ($key) {
                    'date_facture' => $row->date_facture ? Carbon::parse($row->date_facture)->format('d/m/Y') : '',
                    'client'       => $clientNameFor($row),
                    'total_ht'     => $money($row->total_ht),
                    'total_ttc'    => $money($row->total_ttc),
                    'is_paid'      => $row->is_paid ? 'Oui' : 'Non',
                    default        => '',
                };

            case 'avoirs':
                return match ($key) {
                    'created_at' => optional($row->created_at)->format('d/m/Y H:i'),
                    'client'     => isset($row->facture) ? ($row->facture->client ? trim(($row->facture->client->nom_assure ?? '').' '.($row->facture->client->prenom ?? '')) : '') : '',
                    'facture'    => $row->facture ? (($row->facture->titre ?? '') !== '' ? $row->facture->titre : ($row->facture->numero ?? '')) : '',
                    'montant'    => $money($row->montant ?? $row->montant_ht ?? null),
                    default      => '',
                };

            case 'paiements': {
                $date = $row->date ?? ($row->date_paiement ?? $row->created_at);
                $clientLabel = '';
                if ($row->facture) {
                    $clientLabel = trim(($row->facture->client->nom_assure ?? '').' '.($row->facture->client->prenom ?? ''));
                    if ($clientLabel === '') $clientLabel = ($row->facture->prospect_name ?? '');
                }
                $factureLabel = $row->facture ? (($row->facture->titre ?? '') !== '' ? $row->facture->titre : ($row->facture->numero ?? '')) : '';
                return match ($key) {
                    'date_paiement' => $date ? Carbon::parse($date)->format('d/m/Y') : '',
                    'client'        => $clientLabel,
                    'facture'       => $factureLabel,
                    'montant'       => $money($row->montant ?? $row->amount ?? null),
                    'methode'       => $row->methode ?? $row->methode_paiement ?? '',
                    default         => '',
                };
            }

            case 'rdv':
            case 'rdvs':
                return match ($key) {
                    'start_time' => $row->start_time ? Carbon::parse($row->start_time)->format('d/m/Y H:i') : '',
                    'end_time'   => $row->end_time ? Carbon::parse($row->end_time)->format('d/m/Y H:i') : '',
                    'client'     => trim(($row->client->nom_assure ?? '').' '.($row->client->prenom ?? '')),
                    'poseur'     => (string)($row->poseur->nom ?? ''),
                    'title'      => $row->title ?? '',
                    'status'     => $row->status ?? '',
                    default      => '',
                };

            case 'depenses':
            case 'expenses':
                return match ($key) {
                    'date'        => $row->date ? Carbon::parse($row->date)->format('d/m/Y') : '',
                    'client'      => trim(($row->client->nom_assure ?? '').' '.($row->client->prenom ?? '')),
                    'fournisseur' => $row->fournisseur->nom_societe ?? '',
                    'paid_status' => $row->paid_status ?? '',
                    'ht_amount'   => $money($row->ht_amount),
                    'ttc_amount'  => $money($row->ttc_amount),
                    default       => '',
                };

            case 'bons':
            case 'bons_de_commande':
            case 'purchase_orders': {
                $date = $row->date ?? $row->created_at;
                return match ($key) {
                    'date'        => $date ? Carbon::parse($date)->format('d/m/Y') : '',
                    'numero'      => $row->numero ?? '',
                    'fournisseur' => $row->fournisseur->nom_societe ?? '',
                    'total_ht'    => $money($row->total_ht),
                    'total_ttc'   => $money($row->total_ttc),
                    'status'      => $row->status ?? '',
                    default       => '',
                };
            }

            case 'fournisseurs':
                return match ($key) {
                    'created_at' => optional($row->created_at)->format('d/m/Y H:i'),
                    'nom_societe'=> $row->nom_societe ?? '',
                    'contact'    => trim(($row->email ?? '').' / '.($row->telephone ?? '')),
                    default      => '',
                };

            case 'produits':
                return match ($key) {
                    'created_at' => optional($row->created_at)->format('d/m/Y H:i'),
                    'designation'=> ($row->designation ?? $row->name ?? $row->nom ?? ''),
                    'reference'  => $row->reference ?? '',
                    'prix_ht'    => $money($row->prix_ht ?? $row->price_ht ?? $row->prix ?? null),
                    'stock'      => (string)($row->stock ?? $row->quantity ?? ''),
                    default      => '',
                };

            case 'stocks':
                return match ($key) {
                    'created_at' => optional($row->created_at)->format('d/m/Y H:i'),
                    'produit'    => ($row->produit->designation ?? $row->produit->name ?? $row->produit->nom ?? ''),
                    'reference'  => $row->produit->reference ?? '',
                    'quantity'   => (string)($row->quantity ?? $row->qty ?? ''),
                    'location'   => $row->location ?? '',
                    default      => '',
                };

            case 'poseurs':
                return match ($key) {
                    'created_at' => optional($row->created_at)->format('d/m/Y H:i'),
                    'nom'        => (string)($row->nom ?? ''),
                    'contact'    => trim(($row->email ?? '').' / '.($row->telephone ?? '')),
                    default      => '',
                };

            case 'clients':
            default:
                return match ($key) {
                    'created_at' => optional($row->created_at)->format('d/m/Y H:i'),
                    'client'     => trim(($row->nom_assure ?? '').' '.($row->prenom ?? '')),
                    'contact'    => trim(($row->email ?? '').' / '.($row->telephone ?? '')),
                    'vehicule'   => ($row->plaque ?? $row->immatriculation ?? ''),
                    'statut'     => $row->statut ?? $row->statut_gg ?? '-',
                    default      => '',
                };
        }
    }

    /**
     * Small HTML fragment for the modal body.
     * It embeds an iframe that points to the preview endpoint below.
     */
    public function peek(Request $request, string $type, int $id)
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_CLIENT_SERVICE], true), 403);

        if (!in_array($type, ['devis','factures','avoirs'], true)) {
            abort(404);
        }

        $src = route('superadmin.files.preview', ['type'=>$type, 'id'=>$id]);

        // Return a minimal snippet (no separate blade required)
        return response()->make(
            '<div class="h-[70vh]"><iframe src="' . e($src) . '" class="w-full h-full rounded-md border" loading="lazy"></iframe></div>'
        );
    }

    /**
     * Streams the actual PDF for the iframe.
     * Uses your existing tenant PDF views so preview looks identical.
     */
    public function preview(string $type, int $id)
    {
        switch ($type) {
            case 'devis': {
                $devis   = Devis::withoutGlobalScopes()->with(['client','items'])->findOrFail($id);
                $company = optional($devis->client)->company;
                return Pdf::loadView('devis.single-pdf', ['devis'=>$devis,'company'=>$company])
                          ->stream('devis_' . ($devis->numero ?? $devis->id) . '.pdf');
            }
            case 'factures': {
                $facture = Facture::withoutGlobalScopes()
                            ->with(['client','items','devis:id,prospect_name,prospect_email,prospect_phone'])
                            ->findOrFail($id);

                $company = optional($facture->client)->company;
                $logoBase64 = null;
                if ($company && $company->logo) {
                    $logoPath = storage_path('app/public/'.$company->logo);
                    if (file_exists($logoPath)) {
                        $ext  = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $logoBase64 = 'data:image/'.$ext.';base64,'.base64_encode(file_get_contents($logoPath));
                    }
                }

                return Pdf::loadView('factures.pdf', [
                    'facture'    => $facture,
                    'company'    => $company,
                    'logoBase64' => $logoBase64,
                ])->stream('facture_'.($facture->numero ?? $facture->id).'.pdf');
            }
            case 'avoirs': {
                $avoir   = Avoir::withoutGlobalScopes()->with(['facture.client','facture.items'])->findOrFail($id);
                $company = optional(optional($avoir->facture)->client)->company;
                return Pdf::loadView('avoirs.single_pdf', ['avoir'=>$avoir,'company'=>$company])
                          ->stream('avoir_'.$avoir->id.'.pdf');
            }
        }
        abort(404);
    }
}