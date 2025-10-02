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
     * Envoie le contrat du client à la signature (Yousign v3).
     * Prérequis : le PDF est déjà généré dans storage/app/public/{contract_pdf_path}.
     * Si vous préférez, vous pouvez auto-générer si absent (voir bloc optionnel).
     */
    public function send(Request $request, Client $client, YousignService $ys)
    {
        // 0) Garde : e-mail requis
        if (empty($client->email)) {
            return back()->with('error', "Le client n'a pas d’e-mail.");
        }

        // 1) S’assurer que le contrat PDF existe (option A: refuser / option B: générer)
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            // OPTION A (strict) :
            // return back()->with('error', 'Aucun contrat PDF généré pour ce client.');

            // OPTION B (auto-générer) – décommentez si vous avez un ContractController@generate :
            // app(ContractController::class)->generate($request, $client);
            // $client->refresh();
            // if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            //     return back()->with('error', 'Impossible de générer le contrat PDF.');
            // }
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        try {
            $absPath = Storage::disk('public')->path($client->contract_pdf_path);

            // 2) Demande de signature
            $fullname = trim(($client->prenom ?? '') . ' ' . ($client->nom_assure ?? $client->nom ?? ''));
            $title = "Contrat #{$client->id} - {$fullname}";
            $sr = $ys->createSignatureRequest($title, 'email'); // ['id' => '...']

            // 3) Upload du document (pas d’anchors -> fields obligatoires)
            $withAnchors = false;
            $doc = $ys->uploadDocument($sr['id'], $absPath, $withAnchors); // ['id' => '...']

            // 4) Ajout du signataire
            $phone = $client->telephone;
            if ($phone && !preg_match('/^\+\d{6,15}$/', $phone)) {
                $phone = null; // E.164 only
            }

            // Placez la signature à l’endroit exact de votre “Signature du client : ____”
            // Ajustez x/y/width/height selon votre template PDF.
            $payload = [
                'info' => [
                    'first_name'   => $client->prenom ?: 'Client',
                    'last_name'    => $client->nom_assure ?? $client->nom ?? '-',
                    'email'        => $client->email,
                    'phone_number' => $phone,
                    'locale'       => config('services.yousign.locale', 'fr'),
                ],
                'signature_level'               => 'electronic_signature',
                // Sécurité : utilisez otp_email en prod
                'signature_authentication_mode' => app()->environment('production') ? 'otp_email' : 'no_otp',
                'fields' => [[
                    'document_id' => $doc['id'],
                    'type'        => 'signature',
                    'page'        => 1,
                    'x'           => 100,  // <-- ajustez
                    'y'           => 700,  // <-- ajustez (100 était trop en haut)
                    'width'       => 150,
                    'height'      => 40,
                ]],
            ];

            $ys->addSigner($sr['id'], $payload);

            // 5) Activation (envoi e-mail)
            $ys->activate($sr['id']);

            // 6) Persistance
            $client->update([
                'yousign_signature_request_id' => $sr['id'],
                'yousign_document_id'          => $doc['id'] ?? null,
                'statut_gsauto'                => 'sent',
            ]);

            return back()
                ->with('success', 'Document envoyé au client pour signature.')
                ->with('open_signature', true);

        } catch (\Throwable $e) {
            Log::error('Yousign send failed', [
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);
            return back()->with('error', "L'envoi vers Yousign a échoué : ".$e->getMessage());
        }
    }

    /**
     * Relance le client (Yousign renvoie la notification).
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
            Log::error('Yousign resend failed', [
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);
            return back()->with('error', "La relance a échoué : ".$e->getMessage());
        }
    }
}