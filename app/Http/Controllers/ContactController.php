<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    /**
     * Generate the draft contract PDF and store it.
     */
    public function generate(Request $request, Client $client)
    {
        $pdf = Pdf::loadView('contracts.client', [
            'client' => $client,
        ])->setPaper('a4');

        $dir = "contracts/{$client->id}";
        $filename = 'contract.pdf';
        $path = "{$dir}/{$filename}";

        Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put($path, $pdf->output());

        $client->contract_pdf_path = $path;
        $client->statut_gsauto = $client->statut_gsauto ?: 'draft';
        $client->save();

        return back()->with('success', 'Contrat généré.')->with('open_signature', true);
    }

    /**
     * Download the draft contract PDF.
     */
    public function download(Client $client)
    {
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat généré pour ce client.');
        }

        return Storage::disk('public')->download($client->contract_pdf_path, "Contrat-{$client->id}.pdf");
    }

    /**
     * Download the signed contract PDF.
     */
    public function downloadSigned(Client $client)
    {
        if (!$client->contract_signed_pdf_path || !Storage::disk('public')->exists($client->contract_signed_pdf_path)) {
            return back()->with('error', 'Aucun contrat signé disponible pour ce client.');
        }

        return Storage::disk('public')->download($client->contract_signed_pdf_path, "Contrat-Signe-{$client->id}.pdf");
    }

    /**
     * Send the contract for signature via Yousign.
     */
    public function send(Request $request, Client $client, YousignService $yousign)
    {
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Veuillez générer le contrat avant de l’envoyer.');
        }

        try {
            // 1) Create signature request
            $signatureRequest = $yousign->createSignatureRequest("Contrat client #{$client->id}");

            // 2) Upload the contract file
            $fullPath = Storage::disk('public')->path($client->contract_pdf_path);
            $document = $yousign->uploadDocument($signatureRequest['id'], $fullPath, 'contract.pdf');

            // 3) Add the signer
            $yousign->addSigner(
                $signatureRequest['id'],
                $document['id'],
                $client->email,
                $client->prenom,
                $client->nom
            );

            // 4) Activate
            $yousign->activateSignatureRequest($signatureRequest['id']);

            $client->yousign_request_id = $signatureRequest['id'];
            $client->statut_gsauto = 'sent';
            $client->save();

            return back()->with('success', 'Contrat envoyé au client pour signature.')->with('open_signature', true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur lors de l’envoi du contrat : ' . $e->getMessage());
        }
    }

    /**
     * Resend a contract signature request (if failed or expired).
     */
    public function resend(Request $request, Client $client, YousignService $yousign)
    {
        if (!$client->yousign_request_id) {
            return back()->with('error', 'Aucun envoi précédent trouvé pour ce client.');
        }

        try {
            $yousign->resendSignatureRequest($client->yousign_request_id);

            $client->statut_gsauto = 'resent';
            $client->save();

            return back()->with('success', 'Contrat renvoyé au client.')->with('open_signature', true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur lors du renvoi : ' . $e->getMessage());
        }
    }
}