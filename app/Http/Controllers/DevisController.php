<?php

namespace App\Http\Controllers;

use App\Models\Devis;
use App\Models\DevisItem;
use App\Models\Client;
use App\Models\Produit;
use Illuminate\Http\Request;
use App\Exports\DevisExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DevisController extends Controller
{
    public function index()
    {
        $devis = Devis::with('client')->latest()->paginate(10);
        return view('devis.index', compact('devis'));
    }

    public function create()
    {
        $clients  = Client::all();
        $produits = Produit::all();
        return view('devis.create', compact('clients', 'produits'));
    }

    /** Normalize "1,5" → 1.5; trims spaces */
    protected function num($v, $fallback = 0.0): float
    {
        if ($v === null) return (float) $fallback;
        $s = trim((string) $v);
        if ($s === '') return (float) $fallback;
        return (float) str_replace(',', '.', str_replace(' ', '', $s));
    }

    /**
     * Allocate a unique devis number for a company for a given month.
     * Format: SSMMYYYY (01..99 + month + year). Works with client_id null (prospects).
     */
    protected function allocateCompanyMonthNumero(int $companyId, string $date): string
    {
        $dt    = Carbon::parse($date);
        $year  = $dt->format('Y');
        $month = $dt->format('m');

        // Start at current count + 1
        $start = Devis::where('company_id', $companyId)
            ->whereYear('date_devis', $year)
            ->whereMonth('date_devis', $month)
            ->count() + 1;

        // Try until free (guards vs. race conditions)
        for ($seq = max(1, $start); $seq <= 99; $seq++) {
            $numero = str_pad($seq, 2, '0', STR_PAD_LEFT) . $month . $year;

            $exists = Devis::where('company_id', $companyId)
                ->where('numero', $numero)
                ->exists();

            if (! $exists) {
                return $numero;
            }
        }

        throw new \RuntimeException('Aucun numéro de devis disponible pour ce mois.');
    }

    public function store(Request $request)
    {
        // Either an existing client OR a prospect
        $validated = $request->validate([
            'client_id'       => 'nullable|exists:clients,id|required_without:prospect_name',
            'prospect_name'   => 'nullable|string|max:255|required_without:client_id',
            'prospect_email'  => 'nullable|email|max:255',
            'prospect_phone'  => 'nullable|string|max:255',

            'titre'           => 'nullable|string|max:255',
            'date_devis'      => 'required|date',
            'date_validite'   => 'required|date|after_or_equal:date_devis',

            'items'                   => 'required|array|min:1',
            'items.*.produit'         => 'required|string|max:255',
            'items.*.description'     => 'nullable|string',
            'items.*.quantite'        => 'required|numeric|min:0.01',
            'items.*.prix_unitaire'   => 'required|numeric|min:0',
            'items.*.taux_tva'        => 'required|numeric|min:0|max:100',
            'items.*.remise'          => 'nullable|numeric|min:0|max:100',
        ]);

        // Resolve company (works even if it's a prospect)
        $companyId = auth()->user()->company_id
            ?? optional(Client::find($validated['client_id'] ?? null))->company_id
            ?? (int) $request->input('company_id');

        // Create shell devis (we need id for items)
        $devis = new Devis();
        $devis->client_id      = $validated['client_id']      ?? null;  // can be null (prospect)
        $devis->prospect_name  = $validated['prospect_name']  ?? null;
        $devis->prospect_email = $validated['prospect_email'] ?? null;
        $devis->prospect_phone = $validated['prospect_phone'] ?? null;
        $devis->titre          = $validated['titre']          ?? null;
        $devis->date_devis     = $validated['date_devis'];
        $devis->date_validite  = $validated['date_validite'];
        $devis->company_id     = $companyId;

        // Numero: SSMMYYYY unique per company+month
        $devis->numero = $this->allocateCompanyMonthNumero($companyId, $devis->date_devis);
        $devis->total_ht = 0; $devis->total_tva = 0; $devis->total_ttc = 0;
        $devis->save();

        // Totals
        $totalHT  = 0.0;
        $totalTVA = 0.0;

        foreach ($validated['items'] as $item) {
            $qty       = $this->num($item['quantite'], 0);
            $unitPrice = $this->num($item['prix_unitaire'], 0);
            $tauxTva   = $this->num($item['taux_tva'] ?? null, 0);
            $remise    = $this->num($item['remise']   ?? null, 0);

            $lineHT = round($qty * $unitPrice, 2);
            if ($remise > 0) $lineHT = round($lineHT * (1 - $remise / 100), 2);

            $lineTVA = round($lineHT * ($tauxTva / 100), 2);

            $devis->items()->create([
                'produit'       => $item['produit'],
                'description'   => $item['description'] ?? '',
                'quantite'      => $qty,
                'prix_unitaire' => $unitPrice,
                'taux_tva'      => $tauxTva,
                'remise'        => $remise,
                'total_ht'      => $lineHT,
            ]);

            $totalHT  += $lineHT;
            $totalTVA += $lineTVA;
        }

        $devis->update([
            'total_ht'  => round($totalHT, 2),
            'total_tva' => round($totalTVA, 2),
            'total_ttc' => round($totalHT + $totalTVA, 2),
        ]);

        if ($devis->client_id) {
            Client::find($devis->client_id)->update(['statut' => 'Devis généré']);
        }

        return redirect()->route('devis.index')->with('success', 'Devis créé avec succès.');

    }

    public function edit($id)
    {
        $devis    = Devis::with('items')->findOrFail($id);
        $clients  = Client::all();
        $produits = Produit::all();
        return view('devis.edit', compact('devis', 'clients', 'produits'));
    }

    public function update(Request $request, $id)
    {
        $devis = Devis::findOrFail($id);

        $validated = $request->validate([
            'client_id'       => 'nullable|exists:clients,id',
            'prospect_name'   => 'nullable|string|max:255',
            'prospect_email'  => 'nullable|email|max:255',
            'prospect_phone'  => 'nullable|string|max:255',

            'titre'           => 'nullable|string|max:255',
            'date_devis'      => 'required|date',
            'date_validite'   => 'required|date|after_or_equal:date_devis',

            'items'                   => 'required|array|min:1',
            'items.*.produit'         => 'required|string|max:255',
            'items.*.description'     => 'nullable|string',
            'items.*.quantite'        => 'required|numeric|min:0.01',
            'items.*.prix_unitaire'   => 'required|numeric|min:0',
            'items.*.taux_tva'        => 'required|numeric|min:0|max:100',
            'items.*.remise'          => 'nullable|numeric|min:0|max:100',
        ]);

        $oldDate    = $devis->date_devis;
        $oldCompany = (int) $devis->company_id;

        // Keep client OR prospect
        $devis->client_id      = $validated['client_id']      ?? null;
        $devis->prospect_name  = $validated['prospect_name']  ?? null;
        $devis->prospect_email = $validated['prospect_email'] ?? null;
        $devis->prospect_phone = $validated['prospect_phone'] ?? null;
        $devis->titre          = $validated['titre']          ?? null;
        $devis->date_devis     = $validated['date_devis'];
        $devis->date_validite  = $validated['date_validite'];

        if (empty($devis->company_id)) {
            $devis->company_id = auth()->user()->company_id
                ?? optional(Client::find($devis->client_id))->company_id
                ?? (int) $request->input('company_id');
        }

        // Re-allocate number if date or company changed, or if empty
        if (empty($devis->numero)
            || $oldDate !== $devis->date_devis
            || $oldCompany !== (int) $devis->company_id) {
            $devis->numero = $this->allocateCompanyMonthNumero((int) $devis->company_id, $devis->date_devis);
        }

        $devis->save();

        // Rebuild items/totals
        $devis->items()->delete();

        $totalHT  = 0.0;
        $totalTVA = 0.0;

        foreach ($validated['items'] as $item) {
            $qty       = $this->num($item['quantite'], 0);
            $unitPrice = $this->num($item['prix_unitaire'], 0);
            $tauxTva   = $this->num($item['taux_tva'] ?? null, 0);
            $remise    = $this->num($item['remise']   ?? null, 0);

            $lineHT = round($qty * $unitPrice, 2);
            if ($remise > 0) $lineHT = round($lineHT * (1 - $remise / 100), 2);
            $lineTVA = round($lineHT * ($tauxTva / 100), 2);

            $devis->items()->create([
                'produit'       => $item['produit'],
                'description'   => $item['description'] ?? '',
                'quantite'      => $qty,
                'prix_unitaire' => $unitPrice,
                'taux_tva'      => $tauxTva,
                'remise'        => $remise,
                'total_ht'      => $lineHT,
            ]);

            $totalHT  += $lineHT;
            $totalTVA += $lineTVA;
        }

        $devis->update([
            'total_ht'  => round($totalHT, 2),
            'total_tva' => round($totalTVA, 2),
            'total_ttc' => round($totalHT + $totalTVA, 2),
        ]);

        return redirect()->route('devis.index')->with('success', 'Devis mis à jour.');
    }

    public function destroy($id)
    {
        $devis = Devis::findOrFail($id);
        $devis->delete();

        return redirect()->route('devis.index')->with('success', 'Devis supprimé.');
    }

    public function exportExcel()
    {
        return Excel::download(new DevisExport, 'devis.xlsx');
    }

    public function exportPDF()
    {
        $devis   = Devis::with('client')->get();
        $company = auth()->user()->company;

        $pdf = Pdf::loadView('devis.export_pdf', [
            'devis'   => $devis,
            'company' => $company,
        ]);

        return $pdf->download('devis.pdf');
    }

    public function generateFacture(Devis $devis)
    {
        // Your FactureController already allocates its own numero;
        // just pass company_id so numbering works there too.
        $facture = \App\Models\Facture::create([
            'client_id'    => $devis->client_id,
            'devis_id'     => $devis->id,
            'company_id'   => $devis->company_id,
            'date_facture' => $devis->date_devis,
            'total_ht'     => $devis->total_ht,
            'total_ttc'    => $devis->total_ttc,
            'total_tva'    => $devis->total_tva,
        ]);

        foreach ($devis->items as $item) {
            $facture->items()->create([
                'produit'       => $item->produit,
                'description'   => $item->description,
                'quantite'      => $item->quantite,
                'prix_unitaire' => $item->prix_unitaire,
                'taux_tva'      => $item->taux_tva,
                'remise'        => $item->remise,
                'total_ht'      => $item->total_ht,
            ]);
        }

        return redirect()->route('factures.index')->with('success', 'Facture générée depuis le devis.');
    }

public function downloadSinglePdf($id)
{
    $devis   = Devis::with(['client', 'items'])->findOrFail($id);
    $company = auth()->user()->company;

    // Build a safe filename: use numero if present, else the id
    $fileName = 'devis_' . ($devis->numero ?? $devis->id) . '.pdf';

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('devis.single-pdf', [
        'devis'   => $devis,
        'company' => $company,
    ])->download($fileName);
}

public function previewPdf($id)
{
    $devis   = Devis::with(['client', 'items'])->findOrFail($id);
    $company = auth()->user()->company;

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('devis.single-pdf', [
        'devis'   => $devis,
        'company' => $company,
    ]);

    // Stream inline so it can render inside an <iframe>
    return $pdf->stream('devis_' . ($devis->numero ?? $devis->id) . '.pdf');
}

}