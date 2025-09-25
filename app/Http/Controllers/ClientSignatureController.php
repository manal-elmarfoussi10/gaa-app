<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientSignatureController extends Controller
{
    /**
     * Send the client's contract for e-signature (Yousign v3).
     * Prerequisite: contract PDF already generated into storage/app/public/{contract_pdf_path}.
     */
    public function send(Request $request, Client $client, YousignService $ys)
    {
        // Guards
        if (empty($client->email)) {
            return back()->with('error', "Le client n'a pas d’e-mail.");
        }
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        try {
            // Absolute path to the PDF
            $absPath = Storage::disk('public')->path($client->contract_pdf_path);

            // 1) Create Signature Request
            $title = trim("Contrat #{$client->id} - " . trim(($client->prenom ?? '') . ' ' . ($client->nom_assure ?? $client->nom ?? '')));
            $sr = $ys->createSignatureRequest($title, 'email'); // ['id' => '...']

            // 2) Upload the document (no smart anchors in your PDF)
            $withAnchors = false;
            $doc = $ys->uploadDocument($sr['id'], $absPath, $withAnchors); // ['id' => '...']

            // 3) Add signer (+ a field because no anchors)
            $phone = $client->telephone;
            if ($phone && !preg_match('/^\+\d{6,15}$/', $phone)) {
                $phone = null; // Yousign expects E.164
            }

            $payload = [
                'info' => [
                    'first_name'   => $client->prenom ?: 'Client',
                    'last_name'    => $client->nom_assure ?? $client->nom ?? '-',
                    'email'        => $client->email,
                    'phone_number' => $phone,
                    'locale'       => config('services.yousign.locale', 'fr'),
                ],
                'signature_level'               => 'electronic_signature',
                'signature_authentication_mode' => 'no_otp', // or 'otp_email'
                'fields' => [[
                    'document_id' => $doc['id'],
                    'type'        => 'signature',
                    'page'        => 1,
                    'x'           => 100,
                    'y'           => 100,
                    'width'       => 85,
                    'height'      => 40,
                ]],
            ];

            $ys->addSigner($sr['id'], $payload);

            // 4) Activate (send email)
            $ys->activate($sr['id']);

            // 5) Persist IDs + status
            $client->update([
                'yousign_signature_request_id' => $sr['id'],
                'yousign_document_id'          => $doc['id'] ?? null,
                'statut_gsauto'                => 'sent',
            ]);

            return back()
                ->with('success', 'Document envoyé au client pour signature.')
                ->with('open_signature', true);

        } catch (\Throwable $e) {
            Log::error('Yousign send failed', ['client_id' => $client->id, 'error' => $e->getMessage()]);
            return back()->with('error', "L'envoi vers Yousign a échoué : ".$e->getMessage());
        }
    }

    /**
     * Resend / re-activate (Yousign will re-notify the signer).
     */
    public function resend(Request $request, Client $client, YousignService $ys)
    {
        if (!$client->yousign_signature_request_id) {
            return back()->with('error', "Aucune demande de signature Yousign n'est associée à ce client.");
        }

        try {
            $ys->activate($client->yousign_signature_request_id);

            return back()
                ->with('success', 'Rappel envoyé au client.')
                ->with('open_signature', true);

        } catch (\Throwable $e) {
            Log::error('Yousign resend failed', ['client_id' => $client->id, 'error' => $e->getMessage()]);
            return back()->with('error', "La relance a échoué : ".$e->getMessage());
        }
    }
}