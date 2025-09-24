<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    /**
     * Build the contract PDF for a client, store it, and (optionally) return it.
     * POST so we can regenerate safely.
     */
    public function generate(Request $request, Client $client)
    {
        // 1) Render the PDF from a Blade view (see view below)
        $pdf = Pdf::loadView('contracts.client', [
            'client' => $client,
        ])->setPaper('a4');

        // 2) Decide where to save it (public disk so it’s downloadable via /storage)
        $dir = "contracts/{$client->id}";
        $filename = 'contract.pdf';
        $path = "{$dir}/{$filename}";

        // Make sure dir exists & write the file
        Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put($path, $pdf->output());

        // 3) Persist path & mark status as 'draft' (or keep current if already sent)
        $client->contract_pdf_path = $path;              // column from the migration we planned
        $client->statut_gsauto = $client->statut_gsauto ?: 'draft';
        $client->save();

        return back()
            ->with('success', 'Contrat généré.')
            ->with('open_signature', true); // auto-scroll your block
    }

    /**
     * Download the current (non-signed) PDF we generated for this client.
     */
    public function download(Client $client)
    {
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat généré pour ce client.');
        }

        return Storage::disk('public')->download($client->contract_pdf_path, "Contrat-{$client->id}.pdf");
    }

    /**
     * Download the signed contract (filled by webhook after signature).
     */
    public function downloadSigned(Client $client)
    {
        if (!$client->contract_signed_pdf_path || !Storage::disk('public')->exists($client->contract_signed_pdf_path)) {
            return back()->with('error', 'Aucun contrat signé disponible pour ce client.');
        }

        return Storage::disk('public')->download($client->contract_signed_pdf_path, "Contrat-Signe-{$client->id}.pdf");
    }
}