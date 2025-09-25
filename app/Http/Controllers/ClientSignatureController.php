<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientSignatureController extends Controller
{
    /**
     * Send the client's contract for e-signature via Yousign.
     * Requires that $client->contract_pdf_path already exists on the public disk.
     */
    public function send(Request $request, Client $client, YousignService $ys)
    {
        // Guardrails
        if (!$client->email) {
            return back()->with('error', "Le client n'a pas d'e-mail.");
        }
        if (
            !$client->contract_pdf_path ||
            !Storage::disk('public')->exists($client->contract_pdf_path)
        ) {
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        // Absolute path to the generated PDF
        $absPath = Storage::disk('public')->path($client->contract_pdf_path);

        // 1) Create the signature request
        $name = "Contrat #{$client->id} - " . trim(($client->prenom ?? '') . ' ' . ($client->nom ?? ''));
        $sr = $ys->createSignatureRequest($name, 'email');   // returns array with 'id'
        $signatureRequestId = $sr['id'];

        // 2) Upload the PDF
        // set the 3rd arg to true only if you placed smart anchors in the PDF
        $doc = $ys->uploadDocument($signatureRequestId, $absPath, true);

        // 3) Add the signer (the service will drop phone_number if not E.164)
        $payload = [
            'info' => [
                'first_name'   => $client->prenom ?: 'Client',
                'last_name'    => $client->nom ?: '-',
                'email'        => $client->email,
                'phone_number' => $client->telephone ?? null, // optional
                'locale'       => config('services.yousign.locale', 'fr'),
            ],
            'signature_level'               => 'electronic_signature',
            'signature_authentication_mode' => 'no_otp', // or 'otp_email'
            // If you did NOT use smart anchors, specify fields explicitly:
            // 'fields' => [[
            //     'document_id' => $doc['id'],
            //     'type'        => 'signature',
            //     'page'        => 1,
            //     'x'           => 100,
            //     'y'           => 100,
            //     'width'       => 85,
            //     'height'      => 40,
            // ]],
        ];
        $ys->addSigner($signatureRequestId, $payload);

        // 4) Activate the request (triggers the email)
        $ys->activate($signatureRequestId);

        // Persist Yousign state on the client
        $client->update([
            'yousign_request_id' => $signatureRequestId,
            'statut_gsauto'      => 'sent',
        ]);

        return back()
            ->with('success', 'Document envoyé au client pour signature.')
            ->with('open_signature', true);
    }

    /**
     * Resend / re-activate.
     */
    public function resend(Request $request, Client $client, YousignService $ys)
    {
        if (!$client->yousign_request_id) {
            return back()->with('error', "Aucune signature Yousign n'est associée à ce client.");
        }

        $ys->activate($client->yousign_request_id);

        return back()
            ->with('success', 'Rappel envoyé au client.')
            ->with('open_signature', true);
    }
}