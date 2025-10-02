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
     * Prérequis :
     *  - Le PDF du contrat existe dans storage/app/public/{contract_pdf_path}
     *  - La vue Blade contient les Smart Anchors :
     *      [[SIGN_CLIENT]]     (zone signature client)
     *      [[SIGN_COMPANY]]    (zone signature entreprise) ← optionnel
     */
    public function send(Request $request, Client $client, YousignService $ys)
    {
        // 0) Sécurité basique
        if (empty($client->email)) {
            return back()->with('error', "Le client n'a pas d’e-mail.");
        }
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        try {
            // 1) Créer la demande de signature
            $fullname = trim(($client->prenom ?? '').' '.($client->nom_assure ?? $client->nom ?? ''));
            $title    = "Contrat #{$client->id} - {$fullname}";
            $sr       = $ys->createSignatureRequest($title, 'email');  // returns ['id' => '...']

            // 2) Joindre le document avec ANCHORS ACTIVÉS
            //    => le service sait qu’il doit laisser Yousign repérer [[SIGN_CLIENT]] / [[SIGN_COMPANY]]
            $absPath   = Storage::disk('public')->path($client->contract_pdf_path);
            $withAnchors = true;
            $doc       = $ys->uploadDocument($sr['id'], $absPath, $withAnchors); // returns ['id' => '...']

            // 3) Ajouter le signataire (le CLIENT). AUCUN "fields" nécessaire quand anchors = true
            $phone = $client->telephone;
            if ($phone && !preg_match('/^\+\d{6,15}$/', $phone)) {
                $phone = null; // E.164 only
            }

            $ys->addSigner($sr['id'], [
                'info' => [
                    'first_name'   => $client->prenom ?: 'Client',
                    'last_name'    => $client->nom_assure ?? $client->nom ?? '-',
                    'email'        => $client->email,
                    'phone_number' => $phone,
                    'locale'       => config('services.yousign.locale', 'fr'),
                ],
                'signature_level'               => 'electronic_signature',
                // en prod, utilisez 'otp_email' ; en dev, 'no_otp' est pratique
                'signature_authentication_mode' => app()->environment('production') ? 'otp_email' : 'no_otp',
                // Pas de 'fields' ici : les Smart Anchors du PDF pilotent la position.
            ]);

            // (Optionnel) 4) Si vous voulez aussi une signature entreprise :
            //    Décommentez ci-dessous et fournissez un email interne (ENV ou config).
            /*
            $companySignerEmail = config('services.yousign.company_signer_email'); // ex: 'signature@gsauto.com'
            if ($companySignerEmail) {
                $ys->addSigner($sr['id'], [
                    'info' => [
                        'first_name'   => 'Représentant',
                        'last_name'    => 'GS Auto',
                        'email'        => $companySignerEmail,
                        'phone_number' => null,
                        'locale'       => config('services.yousign.locale', 'fr'),
                    ],
                    'signature_level'               => 'electronic_signature',
                    'signature_authentication_mode' => app()->environment('production') ? 'otp_email' : 'no_otp',
                ]);
            }
            */

            // 5) Activer (envoi de l’e-mail)
            $ys->activate($sr['id']);

            // 6) Sauvegarder
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
     * Relance (réactive la demande pour que Yousign renvoie une notification).
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