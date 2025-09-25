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
        // Ensure a PDF exists; generate elsewhere if you prefer
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        $absPath = Storage::disk('public')->path($client->contract_pdf_path);

        // 1) Create the signature request
        $sr = $ys->createSignatureRequest(
            "Contrat #{$client->id} - " . trim(($client->prenom ?? '').' '.($client->nom ?? '')),
            'email' // or 'none' if you plan to embed, per docs
        );

        // 2) Upload the PDF
        $doc = $ys->uploadDocument($sr['id'], $absPath, true); // true = parse anchors if you put them in the PDF

        // 3) Add the signer using THIS client's info
        $payload = [
            'info' => [
                'first_name'   => $client->prenom ?? ($client->nom ?? 'Client'),
                'last_name'    => $client->nom ?? '-',
                'email'        => $client->email,          // <- client’s email here
                'phone_number' => $client->telephone ?? null, // optional
                'locale'       => config('services.yousign.locale', 'fr'),
            ],
            'signature_level'               => 'electronic_signature',
            'signature_authentication_mode' => 'otp_email', // 'no_otp' also works in sandbox
            // If you did NOT use smart anchors in the PDF, add manual fields:
            // 'fields' => [[
            //     'document_id' => $doc['id'],
            //     'type'        => 'signature',
            //     'page'        => 1,
            //     'x'           => 400,
            //     'y'           => 650,
            //     'width'       => 180,
            // ]],
        ];
        $ys->addSigner($sr['id'], $payload);

        // 4) Activate
        $ys->activate($sr['id']);

        // Save request id & status on the client
        $client->yousign_request_id = $sr['id'];
        $client->statut_gsauto      = 'sent';
        $client->save();

        return back()
            ->with('success', 'Document envoyé au client pour signature.')
            ->with('open_signature', true);
    }

    public function resend(Request $request, Client $client, YousignService $yousign)
    {
        // If you stored $client->yousign_signature_request you could re-activate or remind.
        // Yousign v3 doesn’t “resend” via a specific endpoint; usually you re-activate or send reminders.
        // For demo, we simply say OK.
        return back()->with('success', 'Rappel envoyé (simulation).');
    }
}