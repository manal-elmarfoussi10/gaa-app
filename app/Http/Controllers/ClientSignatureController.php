<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientSignatureController extends Controller
{
    public function send(Request $request, Client $client, YousignService $ys)
    {
        if (!$client->email) {
            return back()->with('error', "Le client n'a pas d'e-mail.");
        }
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        $absPath = Storage::disk('public')->path($client->contract_pdf_path);

        // 1) Create SR
        $title = "Contrat #{$client->id} - " . trim(($client->prenom ?? '').' '.($client->nom ?? ''));
        $sr = $ys->createSignatureRequest($title, 'email'); // -> ['id' => 'sr_xxx']

        // 2) Upload PDF (set to true only if your PDF actually contains anchors)
        $doc = $ys->uploadDocument($sr['id'], $absPath, false); // returns ['id' => 'doc_xxx']

        // 3) Add signer — we add a field so the signer “has somewhere to sign”
        // Adjust page/x/y/width/height for your template.
        $first = $client->prenom ?: 'Client';
        $last  = $client->nom ?: '-';

        // Optional phone validation to E.164 (Yousign expects +336..., etc.)
        $phone = $client->telephone;
        if ($phone && !preg_match('/^\+\d{6,15}$/', $phone)) {
            $phone = null; // drop it if not valid; otherwise the API rejects it
        }

        $payload = [
            'info' => [
                'first_name'   => $first,
                'last_name'    => $last,
                'email'        => $client->email,
                'phone_number' => $phone,               // optional, only if valid E.164
                'locale'       => 'fr',
            ],
            // If you are NOT using anchors, you must provide at least one field:
            'fields' => [[
                'document_id' => $doc['id'],
                'type'        => 'signature',
                'page'        => 1,
                'x'           => 100,   // example coordinates
                'y'           => 100,
                'width'       => 85,
                'height'      => 40,
            ]],
            'signature_level'               => 'electronic_signature',
            'signature_authentication_mode' => 'no_otp',  // or 'otp_email'
        ];

        $ys->addSigner($sr['id'], $payload);

        // 4) Activate (send emails)
        $ys->activate($sr['id']);

        // persist
        $client->update([
            'yousign_request_id' => $sr['id'],
            'statut_gsauto'      => 'sent',
        ]);

        return back()
            ->with('success', 'Document envoyé au client pour signature.')
            ->with('open_signature', true);
    }

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