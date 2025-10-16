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
     * Prérequis : le PDF du contrat existe dans storage/app/public/{contract_pdf_path}.
     * -> On place un champ "signature" par coordonnées (page/x/y/width/height).
     */
    public function send(Request $request, Client $client, YousignService $ys)
    {
        // 0) Garde : e-mail requis
        if (empty($client->email)) {
            return back()->with('error', "Le client n'a pas d’e-mail.");
        }

        // 1) Vérifier l'existence du PDF
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat PDF généré pour ce client.');
        }

        try {
            // 2) Préparer
            $absPath  = Storage::disk('public')->path($client->contract_pdf_path);
            $fullname = trim(($client->prenom ?? '') . ' ' . ($client->nom_assure ?? $client->nom ?? ''));
            $title    = "Contrat #{$client->id} - {$fullname}";

            // 3) Créer la demande de signature (v3) et poser external_id = client id
            $sr = $ys->createSignatureRequest($title, 'email', [
                'external_id' => (string) $client->id,
            ]); // renvoie ['id' => '...']

            // 4) Uploader le document (anchors off -> on DOIT fournir des fields)
            $doc = $ys->uploadDocument($sr['id'], $absPath, false); // renvoie ['id' => '...']

            // 5) Ajouter le signataire + champ de signature (coordonnées à ajuster selon votre PDF)
            $signatureField = [
                'type'        => 'signature',
                'document_id' => $doc['id'],
                'page'        => 2, // stay on second page
                'shape'       => [  // coordinates must be inside shape
                    'x'      => 120,
                    'y'      => 660,  // increase to move up, decrease to move down
                    'width'  => 180,
                    'height' => 45,
                ],
            ];
            
            
            // Yousign attend un phone en E.164 si fourni ; sinon laissez null
            $phone = $client->telephone;
            if ($phone && !preg_match('/^\+\d{6,15}$/', $phone)) {
                $phone = null;
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
                'signature_authentication_mode' => 'no_otp',
                'fields' => [$signatureField],
            ]);

            // 6) Activer (envoi de l’e-mail Yousign)
            $ys->activate($sr['id']);

            // 7) Persister les IDs + reset statut_signature
            $client->update([
                'yousign_signature_request_id' => $sr['id'],
                'yousign_document_id'          => $doc['id'] ?? null,
                'statut_gsauto'                => 'sent',
                'statut_signature'             => 0,
            ]);

            return back()
                ->with('success', 'Document envoyé au client pour signature.')
                ->with('open_signature', true);

        } catch (\Throwable $e) {
            Log::error('Yousign send failed', [
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);

            // Erreur fréquente si aucun 'field' n’est associé : "signer.field_required"
            return back()->with('error', "L'envoi vers Yousign a échoué : ".$e->getMessage());
        }
    }

    /**
     * Relance la demande de signature (renvoie la notif au client).
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

    /**
     * Télécharger le contrat signé (essaie local, sinon rapatrie depuis Yousign puis stocke).
     */
    public function downloadSigned(Client $client, YousignService $ys)
    {
        // 1) Si déjà stocké localement → on télécharge
        $localPath = $client->contract_signed_pdf_path ?? $client->signed_pdf_path;
        if ($localPath && Storage::disk('public')->exists($localPath)) {
            return Storage::disk('public')->download($localPath, "Contrat-signe-{$client->id}.pdf");
        }

        // 2) Sinon, tenter de le récupérer depuis Yousign si on a les IDs
        $srId  = $client->yousign_signature_request_id ?? null; // (accessor ok aussi: $client->yousign_request_id)
        $docId = $client->yousign_document_id ?? null;

        if ($srId && $docId) {
            try {
                $pdf = $ys->downloadSignedDocument($srId, $docId); // renvoie les octets PDF

                $savePath = "contracts/{$client->id}/contract-signed.pdf";
                Storage::disk('public')->put($savePath, $pdf);

                // Persister pour les prochains téléchargements
                $client->update([
                    'signed_pdf_path'          => $savePath, // legacy
                    'contract_signed_pdf_path' => $savePath, // nouvelle colonne utilisée par l'accessor
                ]);

                // Et renvoyer le fichier directement
                return Storage::disk('public')->download($savePath, "Contrat-signe-{$client->id}.pdf");
            } catch (\Throwable $e) {
                Log::warning('Yousign downloadSigned failed', [
                    'client_id' => $client->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return back()->with('error', "Contrat signé introuvable pour ce client.");
    }
}