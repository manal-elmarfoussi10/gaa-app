<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientSignatureController extends Controller
{
    /**
     * Send the client's contract for e-signature via Yousign v3.
     * Requires: clients.contract.generate already created a PDF at storage/public/{contract_pdf_path}
     */
    public function send(Request $request, Client $client, YousignService $ys)
    {
        // Guardrails
        if (!$client->email) {
            return back()->with('error', "Le client n'a pas d’e-mail.");
        }
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        // Absolute path to the already-generated PDF
        $absPath = Storage::disk('public')->path($client->contract_pdf_path);

        // 1) Create a Signature Request
        $title = trim("Contrat #{$client->id} - " . trim(($client->prenom ?? '').' '.($client->nom_assure ?? $client->nom ?? '')));
        $deliveryMode = 'email'; // or 'none' if you handle the link yourself
        $signatureRequest = $ys->createSignatureRequest($title, $deliveryMode); // ['id' => 'sr_xxx']

        // 2) Upload the document (set $withAnchors=true only if your PDF actually contains anchors)
        $withAnchors = false;
        $document = $ys->uploadDocument($signatureRequest['id'], $absPath, $withAnchors); // ['id' => 'doc_xxx']

        // 3) Add a signer (+ at least one field if not using anchors)
        $firstName = $client->prenom ?: 'Client';
        $lastName  = $client->nom_assure ?? $client->nom ?? '-';

        // Optional phone number -> only keep if E.164, otherwise Yousign rejects it
        $phone = $client->telephone;
        if ($phone && !preg_match('/^\+\d{6,15}$/', $phone)) {
            $phone = null;
        }

        $signerPayload = [
            'info' => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'email'        => $client->email,
                'phone_number' => $phone,
                'locale'       => config('services.yousign.locale', 'fr'),
            ],
            'signature_level'               => 'electronic_signature',
            'signature_authentication_mode' => 'no_otp', // or 'otp_email'
        ];

        // If you did NOT use anchors when uploading, you MUST specify at least one field
        if (!$withAnchors) {
            $signerPayload['fields'] = [[
                'document_id' => $document['id'],
                'type'        => 'signature',
                'page'        => 1,
                // Adjust coordinates to your template
                'x'           => 100,
                'y'           => 100,
                'width'       => 85,
                'height'      => 40,
            ]];
        }

        $ys->addSigner($signatureRequest['id'], $signerPayload);

        // 4) Activate (sends the email to the signer)
        $ys->activate($signatureRequest['id']);

        // Persist Yousign state on the client (match your DB columns)
        $client->update([
            'yousign_signature_request_id' => $signatureRequest['id'],
            // optionally keep the uploaded document id if your service returns it and you want to store it
            // 'yousign_document_id'          => $document['id'] ?? null,
            'statut_gsauto'                => 'sent',
        ]);

        return back()
            ->with('success', 'Document envoyé au client pour signature.')
            ->with('open_signature', true);
    }

    /**
     * Resend / re-activate the request (Yousign will re-notify the signer).
     */
    public function resend(Request $request, Client $client, YousignService $ys)
    {
        if (!$client->yousign_signature_request_id) {
            return back()->with('error', "Aucune signature Yousign n'est associée à ce client.");
        }

        $ys->activate($client->yousign_signature_request_id);

        return back()
            ->with('success', 'Rappel envoyé au client.')
            ->with('open_signature', true);
    }
}