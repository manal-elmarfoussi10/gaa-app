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
        // 1) Render HTML -> PDF (adjust the Blade view path/HTML as you like)
        $pdf = Pdf::loadView('contracts.contract', [
            'client' => $client,
            'today'  => now(),
        ])->setPaper('a4');

        // 2) Persist to storage/app/contracts/{id}.pdf
        $relative = "contracts/{$client->id}.pdf";
        Storage::put($relative, $pdf->output());

        // 3) Save the path in DB
        $client->update([
            'contract_pdf_path' => $relative, // non signed
        ]);

        // 4) Redirect back with a message
        return back()->with('success', 'Contrat généré.')->with('open_signature', true);
    }

    /**
     * Download the current (non-signed) PDF we generated for this client.
     */
    public function download(Client $client)
    {
        abort_unless($client->contract_pdf_path && Storage::exists($client->contract_pdf_path), 404, 'PDF introuvable');
        return response()->download(Storage::path($client->contract_pdf_path), "contrat-{$client->id}.pdf");
    }

    /**
     * (Optional) Download the signed file if/when you save it later from a webhook.
     */
    public function downloadSigned(Client $client)
    {
        abort_unless($client->signed_pdf_path && Storage::exists($client->signed_pdf_path), 404, 'PDF signé introuvable');
        return response()->download(Storage::path($client->signed_pdf_path), "contrat-signe-{$client->id}.pdf");
    }
}