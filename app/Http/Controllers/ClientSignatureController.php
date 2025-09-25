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
        // Basic guardrails
        if (!$client->email) {
            return back()->with('error', "Le client n'a pas d'e-mail.");
        }
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        // Absolute path to the PDF we already generated
        $absPath = Storage::disk('public')->path($client->contract_pdf_path);

        // 1) Create a signature request
        $title = trim("Contrat #{$client->id} - " . trim(($client->prenom ?? '').' '.($client->nom_assure ?? $client->nom ?? '')));
        $sr = $ys->createRequest($title);
        $signatureRequestId = $sr['id'];

        // 2) Upload the PDF document
        $doc = $ys->uploadDocument($signatureRequestId, $absPath);
        $documentId = $doc['id'];

        // 3) Add the signer (use the current client's info)
        $ys->addSigner($signatureRequestId, $documentId, [
            'prenom' => $client->prenom ?? $client->nom_assure ?? 'Client',
            'nom'    => $client->nom_assure ?? $client->nom ?? '-',
            'email'  => $client->email,
            'phone'  => $client->telephone ?? null, // optional
        ]);

        // 4) Activate the request (triggers the email to the signer)
        $ys->activate($signatureRequestId);

        // Persist Yousign state on the client
        $client->update([
            'yousign_request_id' => $signatureRequestId,   // make sure this column exists
            'statut_gsauto'      => 'sent',
        ]);

        return back()
            ->with('success', 'Document envoyé au client pour signature.')
            ->with('open_signature', true);
    }

    /**
     * (Optional) Resend / re-activate logic.
     * Yousign v3 doesn’t have a special “resend” endpoint; usually you send reminders or re-activate.
     * Here we simply try to re-activate if we have the request id.
     */
    public function resend(Request $request, Client $client, YousignService $ys)
    {
        if (!$client->yousign_request_id) {
            return back()->with('error', "Aucune signature Yousign n'est associée à ce client.");
        }

        // Re-activate the existing request (safe in sandbox)
        $ys->activate($client->yousign_request_id);

        return back()
            ->with('success', 'Rappel envoyé au client.')
            ->with('open_signature', true);
    }
}