<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\FactureItem;
use App\Models\Paiement;
use App\Models\Produit;
use App\Exports\FacturesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Carbon\Carbon;

class FactureController extends Controller
{
    public function index()
    {
        $factures = Facture::with([
            'client:id,nom_assure',
            'devis:id,prospect_name'
        ])->latest()->get();

        return view('factures.index', compact('factures'));
    }

    public function create()
    {
        $clients  = Client::all();
        $devis    = Devis::all();
        $produits = Produit::all();
    
        $defaults = $this->paymentDefaultsFromCompany(); // <—
    
        return view('factures.create', compact('clients', 'devis', 'produits', 'defaults'));
    }
    /** Normalize "1,5" → 1.5; trims spaces */
    protected function num($v, $fallback = 0.0): float
    {
        if ($v === null) return (float) $fallback;
        $s = trim((string) $v);
        if ($s === '') return (float) $fallback;
        return (float) str_replace(',', '.', str_replace(' ', '', $s));
    }

    /** Build a unique invoice number per company & day (ddmmyyyy[-n]) */
 /** Build invoice number as SSMMYYYY (seq per client per month) */
protected function buildClientMonthYearNumber(?int $clientId, string $date): string
{
    $dt    = Carbon::parse($date);
    $year  = $dt->format('Y');
    $month = $dt->format('m');

    $query = Facture::query()
        ->whereYear('date_facture', $year)
        ->whereMonth('date_facture', $month);

    if ($clientId) {
        $query->where('client_id', $clientId);
    } else {
        $query->whereNull('client_id');
    }

    $seq = $query->count() + 1;

    // Format: SSMMYYYY (e.g. 01092025, 02092025)
    return str_pad($seq, 2, '0', STR_PAD_LEFT) . $month . $year;
}

 
public function store(Request $request)
{
    $validated = $request->validate([
        'client_id'      => 'nullable|exists:clients,id|required_without:prospect_name',
        'prospect_name'  => 'nullable|string|max:255|required_without:client_id',
        'prospect_email' => 'nullable|email|max:255',
        'prospect_phone' => 'nullable|string|max:255',

        'devis_id'       => 'nullable|exists:devis,id',
        'titre'          => 'nullable|string|max:255',
        'date_facture'   => 'required|date',

        // Items
        'items'                 => 'required|array|min:1',
        'items.*.produit'       => 'required|string|max:255',
        'items.*.description'   => 'nullable|string',
        'items.*.quantite'      => 'required|numeric|min:0',
        'items.*.prix_unitaire' => 'required|numeric|min:0',
        'items.*.taux_tva'      => 'required|numeric|min:0',
        'items.*.remise'        => 'nullable|numeric|min:0|max:100',

        // Payment terms
        'payment_method'     => 'nullable|string|max:255',
        'payment_iban'       => 'nullable|string|max:255',
        'payment_bic'        => 'nullable|string|max:255',
        'penalty_rate'       => 'nullable|numeric',
        'payment_terms_text' => 'nullable|string',
        'due_date'           => 'nullable|date',
    ]);

    // Resolve company (works for client or prospect)
    $company = auth()->user()->company
        ?? optional(Client::find($request->client_id))->company
        ?? optional(Devis::find($request->devis_id))->company;

    $companyId = $company?->id
        ?? (int) $request->input('company_id');

    // Build defaults if some payment fields are empty
    $defaults = $this->defaultPaymentTerms($company, $request->input('due_date'));

    $facture               = new Facture();
    $facture->client_id    = $request->client_id;   // may be null (prospect)
    $facture->devis_id     = $request->devis_id;
    $facture->titre        = $request->titre;
    $facture->date_facture = $request->date_facture;
    $facture->company_id   = $companyId;

    // Numero SSMMYYYY unique per company+month
    $facture->numero = $this->allocateCompanyMonthNumero($companyId, $facture->date_facture);

    // Prospect snapshot
    $facture->prospect_name  = $request->prospect_name;
    $facture->prospect_email = $request->prospect_email;
    $facture->prospect_phone = $request->prospect_phone;

    // Save payment terms (use form value if provided, else defaults)
    $facture->payment_method     = $request->input('payment_method',     $defaults['payment_method']);
    $facture->payment_iban       = $request->input('payment_iban',       $defaults['payment_iban']);
    $facture->payment_bic        = $request->input('payment_bic',        $defaults['payment_bic']);
    $facture->penalty_rate       = $request->input('penalty_rate',       $defaults['penalty_rate']);
    $facture->payment_terms_text = $request->input('payment_terms_text', $defaults['payment_terms_text']);
    $facture->due_date           = $request->input('due_date',           $defaults['due_date']);

    // Totals
    $totalHT  = 0.0;
    $totalTVA = 0.0;
    foreach ($request->items as $row) {
        $pu       = $this->num($row['prix_unitaire'], 0);
        $qty      = $this->num($row['quantite'], 0);
        $discount = $this->num($row['remise'] ?? null, 0);
        $tvaRate  = $this->num($row['taux_tva'] ?? null, 20);

        $lineHT = round($pu * $qty, 2);
        if ($discount > 0) $lineHT = round($lineHT * (1 - $discount / 100), 2);
        $lineTVA = round($lineHT * ($tvaRate / 100), 2);

        $totalHT  += $lineHT;
        $totalTVA += $lineTVA;
    }

    $facture->total_ht  = round($totalHT, 2);
    $facture->tva       = 20;
    $facture->total_tva = round($totalTVA, 2);
    $facture->total_ttc = round($facture->total_ht + $facture->total_tva, 2);
    $facture->save();

    foreach ($request->items as $row) {
        $pu       = $this->num($row['prix_unitaire'], 0);
        $qty      = $this->num($row['quantite'], 0);
        $discount = $this->num($row['remise'] ?? null, 0);
        $tvaRate  = $this->num($row['taux_tva'] ?? null, 20);

        $lineHT = round($pu * $qty, 2);
        if ($discount > 0) $lineHT = round($lineHT * (1 - $discount / 100), 2);

        FactureItem::create([
            'facture_id'    => $facture->id,
            'produit'       => $row['produit'],
            'description'   => $row['description'] ?? null,
            'quantite'      => $qty,
            'prix_unitaire' => round($pu, 2),
            'taux_tva'      => $tvaRate,
            'remise'        => $discount,
            'total_ht'      => $lineHT,
        ]);
    }

    return redirect()->route('factures.index')->with('success', 'Facture créée avec succès.');
}


public function edit(Facture $facture)
{
    $clients  = Client::all();
    $devis    = Devis::all();
    $produits = Produit::all();

    $defaults = $this->paymentDefaultsFromCompany(); // <—

    return view('factures.edit', compact('facture', 'clients', 'devis', 'produits', 'defaults'));
}

    
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'client_id'               => 'nullable|exists:clients,id',
        'devis_id'                => 'nullable|exists:devis,id',
        'titre'                   => 'nullable|string|max:255',
        'date_facture'            => 'required|date',

        'items'                   => 'required|array|min:1',
        'items.*.produit'         => 'required|string|max:255',
        'items.*.quantite'        => 'required|numeric|min:0',
        'items.*.prix_unitaire'   => 'required|numeric|min:0',
        'items.*.taux_tva'        => 'nullable',
        'items.*.remise'          => 'nullable|numeric|min:0|max:100',

        // Payment terms
        'payment_method'     => 'nullable|string|max:255',
        'payment_iban'       => 'nullable|string|max:255',
        'payment_bic'        => 'nullable|string|max:255',
        'penalty_rate'       => 'nullable|numeric',
        'payment_terms_text' => 'nullable|string',
        'due_date'           => 'nullable|date',
    ]);

    $facture = Facture::findOrFail($id);

    $oldDate    = $facture->date_facture;
    $oldCompany = (int) $facture->company_id;

    $facture->client_id    = $request->client_id;
    $facture->devis_id     = $request->devis_id;
    $facture->titre        = $request->titre;
    $facture->date_facture = $request->date_facture;

    if (empty($facture->company_id)) {
        $facture->company_id = auth()->user()->company_id
            ?? optional(Client::find($request->client_id))->company_id
            ?? optional(Devis::find($request->devis_id))->company_id
            ?? (int) $request->input('company_id');
    }

    // Payment terms (overwrite with new form values)
    $facture->payment_method     = $request->input('payment_method');
    $facture->payment_iban       = $request->input('payment_iban');
    $facture->payment_bic        = $request->input('payment_bic');
    $facture->penalty_rate       = $request->input('penalty_rate');
    $facture->payment_terms_text = $request->input('payment_terms_text');
    $facture->due_date           = $request->input('due_date');

    // Re-allocate number if date or company changed, or if empty
    if (empty($facture->numero)
        || $oldDate !== $facture->date_facture
        || $oldCompany !== (int) $facture->company_id) {
        $facture->numero = $this->allocateCompanyMonthNumero((int) $facture->company_id, $facture->date_facture);
    }

    // Totals
    $totalHT  = 0.0;
    $totalTVA = 0.0;
    foreach ($request->items as $row) {
        $pu       = $this->num($row['prix_unitaire'], 0);
        $qty      = $this->num($row['quantite'], 0);
        $discount = $this->num($row['remise'] ?? null, 0);
        $tvaRate  = $this->num($row['taux_tva'] ?? null, 20);

        $lineHT = round($pu * $qty, 2);
        if ($discount > 0) $lineHT = round($lineHT * (1 - $discount / 100), 2);
        $lineTVA = round($lineHT * ($tvaRate / 100), 2);

        $totalHT  += $lineHT;
        $totalTVA += $lineTVA;
    }

    $facture->total_ht  = round($totalHT, 2);
    $facture->tva       = 20;
    $facture->total_tva = round($totalTVA, 2);
    $facture->total_ttc = round($facture->total_ht + $facture->total_tva, 2);
    $facture->save();

    // Replace items
    $facture->items()->delete();
    foreach ($request->items as $row) {
        $pu       = $this->num($row['prix_unitaire'], 0);
        $qty      = $this->num($row['quantite'], 0);
        $discount = $this->num($row['remise'] ?? null, 0);
        $tvaRate  = $this->num($row['taux_tva'] ?? null, 20);

        $lineHT = round($pu * $qty, 2);
        if ($discount > 0) $lineHT = round($lineHT * (1 - $discount / 100), 2);

        $facture->items()->create([
            'produit'       => $row['produit'],
            'description'   => $row['description'] ?? null,
            'quantite'      => $qty,
            'prix_unitaire' => round($pu, 2),
            'taux_tva'      => $tvaRate,
            'remise'        => $discount,
            'total_ht'      => $lineHT,
        ]);
    }

    return redirect()->route('factures.index')->with('success', 'Facture mise à jour avec succès.');
}



    public function exportExcel()
    {
        return Excel::download(new FacturesExport, 'factures.xlsx');
    }

    public function exportFacturesPDF()
    {
        try {
            $factures = Facture::with([
                'client:id,nom_assure',
                'devis:id,prospect_name'
            ])->get();

            $user    = auth()->user();
            $company = $user->company ?? (object) [
                'name'    => 'Votre Société',
                'address' => 'Adresse non définie',
                'phone'   => '',
                'email'   => '',
                'logo'    => null,
            ];

            $logoBase64 = null;
            if ($company->logo) {
                try {
                    $logoPath = storage_path('app/public/' . $company->logo);
                    if (file_exists($logoPath)) {
                        $type       = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $data       = file_get_contents($logoPath);
                        $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Logo processing error: '.$e->getMessage());
                }
            }

            $pdf = DomPDF::loadView('factures.export_pdf', [
                'factures'   => $factures,
                'company'    => $company,
                'logoBase64' => $logoBase64,
            ]);

            return $pdf->download('liste_factures_' . now()->format('Ymd_His') . '.pdf');

        } catch (\Throwable $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la génération du PDF');
        }
    }

    public function downloadPdf($id)
    {
        $facture = Facture::with(['client', 'items', 'devis:id,prospect_name,prospect_email,prospect_phone'])->findOrFail($id);
        $user    = auth()->user();

        $company = $user->company ?? (object) [
            'name'    => 'Votre Société',
            'address' => 'Adresse non définie',
            'phone'   => '',
            'email'   => '',
            'logo'    => null,
        ];

        $logoBase64 = null;
        if ($company->logo) {
            $logoPath = storage_path('app/public/' . $company->logo);
            if (file_exists($logoPath)) {
                $type       = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data       = file_get_contents($logoPath);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $pdf = DomPDF::loadView('factures.pdf', [
            'facture'    => $facture,
            'company'    => $company,
            'logoBase64' => $logoBase64,
        ]);

        return $pdf->download("facture_{$facture->numero}.pdf");
    }

/**
 * Allocate a unique invoice number for a company for a given month.
 * Format: SSMMYYYY (01..99 + month + year).
 * Works for both clients and prospects (client_id may be null).
 */
protected function allocateCompanyMonthNumero(int $companyId, string $date): string
{
    $dt    = \Carbon\Carbon::parse($date);
    $year  = $dt->format('Y');
    $month = $dt->format('m');

    // Start from current count + 1 for this company+month
    $start = Facture::where('company_id', $companyId)
        ->whereYear('date_facture', $year)
        ->whereMonth('date_facture', $month)
        ->count() + 1;

    // Try sequentially until we find a free spot (race-safe retry loop)
    for ($seq = max(1, $start); $seq <= 99; $seq++) {
        $numero = str_pad($seq, 2, '0', STR_PAD_LEFT) . $month . $year;

        $exists = Facture::where('company_id', $companyId)
            ->where('numero', $numero)
            ->exists();

        if (! $exists) {
            return $numero;
        }
    }

    throw new \RuntimeException('Aucun numéro disponible pour ce mois.');
}

    public function acquitter($id)
    {
        $facture = Facture::with(['paiements', 'avoirs'])->findOrFail($id);

        $totalPaye  = (float) $facture->paiements->sum('montant');
        $totalAvoir = (float) $facture->avoirs->sum('montant');
        $reste      = round((float) $facture->total_ttc - $totalPaye - $totalAvoir, 2);

        if ($reste > 0) {
            Paiement::create([
                'facture_id' => $facture->id,
                'montant'    => $reste,
                'mode'       => 'Virement',
                'date'       => now(),
            ]);
        }

        return redirect()->route('factures.index')->with('success', 'Facture acquittée.');
    }

    /**
 * Build sane defaults for payment terms from the company profile.
 */
private function paymentDefaultsFromCompany(): array
{
    $company = auth()->user()->company;

    $name   = $company->commercial_name ?: $company->name ?: 'Votre société';
    $iban   = $company->iban ?? '';
    $bic    = $company->bic ?? '';
    $method = $company->payment_method ?? 'Virement bancaire';
    $penalty= $company->penalty_rate ?? '';
    $due    = now()->addDays(30); // default +30j

    // Build the printable text block with the *company name* and banking info
    $text  = "Par virement bancaire ou chèque à l'ordre de {$name}\n";
    if ($bic)  { $text .= "Code B.I.C : {$bic}\n"; }
    if ($iban) { $text .= "Code I.B.A.N : {$iban}\n"; }
    $text .= "La présente facture sera payable au plus tard le : ".$due->format('d/m/Y')."\n";
    $text .= "Passé ce délai, sans obligation d’envoi d’une relance, une pénalité sera appliquée conformément au Code de commerce.\n";
    $text .= "Une indemnité forfaitaire pour frais de recouvrement de 40€ est également exigible.";

    return [
        'payment_method'     => $method,
        'payment_iban'       => $iban,
        'payment_bic'        => $bic,
        'penalty_rate'       => $penalty,
        'due_date'           => $due->toDateString(),
        'payment_terms_text' => $text,
        // Also give the name in case you need it in the Blade
        'company_name'       => $name,
    ];
}
}