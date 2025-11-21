<?php

namespace App\Http\Controllers;

use App\Models\Avoir;
use App\Models\Facture;
use Illuminate\Http\Request;
use App\Exports\AvoirsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AvoirController extends Controller
{
    /**
     * List all avoirs.
     */
    public function index()
    {
        // Load everything we display to avoid N+1 and null surprises
        $avoirs = Avoir::with([
            'facture.client',    // client may be null (prospect)
            'facture.paiements',
            'facture.avoirs',
        ])->latest()->get();

        return view('avoirs.index', compact('avoirs'));
    }

    /**
     * Create form (optional facture preselection).
     */
    public function create(Request $request)
    {
        $facture = null;
        if ($request->filled('facture_id')) {
            $facture = Facture::with(['paiements', 'avoirs', 'client'])
                ->findOrFail($request->facture_id);
        }

        $factures = Facture::with('client')->latest()->get();

        return view('avoirs.create', compact('factures', 'facture'));
    }

    /**
     * Create form via /avoirs/create/{facture}.
     */
    public function createFromFacture(Facture $facture)
    {
        $facture->load(['paiements', 'avoirs', 'client']);
        $factures = Facture::with('client')->latest()->get();

        return view('avoirs.create', compact('factures', 'facture'));
    }

    /**
     * Store a new avoir with validation against remaining balance.
     */
    public function store(Request $request)
    {
        $request->validate([
            'facture_id' => ['required', 'exists:factures,id'],
            'montant'    => ['required', 'numeric', 'min:0.01'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        $facture = Facture::with(['paiements', 'avoirs'])->findOrFail($request->facture_id);

        $totalPaye  = (float) $facture->paiements->sum('montant');
        $totalAvoir = (float) $facture->avoirs->sum('montant');
        $reste      = round((float) $facture->total_ttc - $totalPaye - $totalAvoir, 2);

        $montant = round((float) $request->montant, 2);
        if ($montant > $reste) {
            return back()
                ->withErrors(['montant' => "Le montant de l’avoir ({$montant} €) dépasse le reste dû ({$reste} €)."])
                ->withInput();
        }

        Avoir::create([
            'facture_id' => $facture->id,
            'montant'    => $montant,
            'notes'      => $request->notes,
        ]);

        return redirect()->route('factures.index')->with('success', 'Avoir enregistré.');
    }

    public function show(Avoir $avoir)
    {
        $avoir->load('facture.client');
        return view('avoirs.show', compact('avoir'));
    }

    public function edit(Avoir $avoir)
    {
        $avoir->load('facture.client', 'facture.paiements', 'facture.avoirs');
        $factures = Facture::with('client')->latest()->get();

        return view('avoirs.edit', compact('avoir', 'factures'));
    }

    public function update(Request $request, Avoir $avoir)
    {
        $request->validate([
            'facture_id' => ['required', 'exists:factures,id'],
            'montant'    => ['required', 'numeric', 'min:0.01'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        $facture = Facture::with(['paiements', 'avoirs'])->findOrFail($request->facture_id);

        // Recompute reste ignoring the current avoir so we can keep same amount
        $totalPaye  = (float) $facture->paiements->sum('montant');
        $totalAvoir = (float) $facture->avoirs->where('id', '!=', $avoir->id)->sum('montant');
        $reste      = round((float) $facture->total_ttc - $totalPaye - $totalAvoir, 2);

        $montant = round((float) $request->montant, 2);
        if ($montant > $reste) {
            return back()
                ->withErrors(['montant' => "Le montant de l’avoir ({$montant} €) dépasse le reste dû ({$reste} €)."])
                ->withInput();
        }

        $avoir->update([
            'facture_id' => $facture->id,
            'montant'    => $montant,
            'notes'      => $request->notes,
        ]);

        return redirect()->route('avoirs.index')->with('success', 'Avoir modifié avec succès.');
    }

    public function destroy(Avoir $avoir)
    {
        $avoir->delete();
        return redirect()->route('avoirs.index')->with('success', 'Avoir supprimé.');
    }

    public function exportExcel()
    {
        return Excel::download(new AvoirsExport, 'avoirs.xlsx');
    }

    public function exportPDF()
    {
        $avoirs = Avoir::with('facture.client.rdvs')->get();
        $pdf = Pdf::loadView('avoirs.pdf', compact('avoirs'));
        return $pdf->download('avoirs.pdf');
    }

    /**
     * Download a single avoir PDF (file).
     */
    public function export_PDF(Avoir $avoir)
    {
        $avoir->load('facture.client', 'facture.items');
        $company = auth()->user()->company ?? null;

        $pdf = Pdf::loadView('avoirs.single_pdf', compact('avoir', 'company'));
        return $pdf->download("avoir_{$avoir->id}.pdf");
    }

    /**
     * Stream a single avoir PDF for modal preview.
     */
    public function previewPdf($id)
    {
        $avoir   = Avoir::with(['facture.client', 'facture.items'])->findOrFail($id);
        $company = auth()->user()->company ?? null;

        // Use the SINGLE avoir template (same layout as download)
        $pdf = Pdf::loadView('avoirs.single_pdf', [
            'avoir'   => $avoir,
            'company' => $company,
        ]);

        return $pdf->stream("avoir_{$avoir->id}.pdf");
    }
}