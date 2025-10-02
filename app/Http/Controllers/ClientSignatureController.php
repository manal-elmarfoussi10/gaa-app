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
     * -> Nous plaçons un champ "signature" par coordonnées (page/x/y/width/height).
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

            // 3) Créer la demande de signature
            // 3) Créer la demande de signature (v3) en posant external_id = client id
$sr = $ys->createSignatureRequest($title, 'email', [
    'external_id' => (string) $client->id,
]);// renvoie ['id' => '...']

            // 4) Uploader le document (on désactive les anchors => on DOIT fournir des 'fields')
            $withAnchors = false;
            $doc = $ys->uploadDocument($sr['id'], $absPath, $withAnchors); // renvoie ['id' => '...']

            // 5) Ajouter le signataire avec un champ signature "manuel"
            //    Ajustez les coordonnées si nécessaire :
            //    - page : 1 (signature client est en bas de la page 1)
            //    - x/y  : décalage en pixels (plus y est grand, plus on descend sur la page)
            //    - width/height : taille du rectangle de signature
            $signatureField = [
                'document_id' => $doc['id'],
                'type'        => 'signature',
                'page'        => 2,
                'x'           => 120, // ← Ajustez finement selon votre rendu PDF
                'y'           => 680, // ← Descendre/monter par pas de 15–30 si besoin
                'width'       => 180,
                'height'      => 45,
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
                'signature_authentication_mode' => app()->environment('production') ? 'otp_email' : 'no_otp',
                'fields' => [$signatureField],
            ]);

            // 6) Activer (envoi de l’e-mail Yousign)
            $ys->activate($sr['id']);

            // 7) Persister les IDs
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

    public function downloadSigned(Client $client, YousignService $ys)
{
    // 1) If stored locally → stream it
    $path = $client->contract_signed_pdf_path ?? $client->signed_pdf_path;
    if ($path && Storage::disk('public')->exists($path)) {
        return Response::download(Storage::disk('public')->path($path), "Contrat-signe-{$client->id}.pdf");
    }

    // 2) Else try to fetch from Yousign (and save)
    if ($client->yousign_request_id && $client->yousign_document_id) {
        $pdf = $ys->downloadSignedDocument($client->yousign_request_id, $client->yousign_document_id);

        $savePath = "contracts/{$client->id}/contract-signed.pdf";
        Storage::disk('public')->put($savePath, $pdf);

        $client->update([
            'signed_pdf_path'          => $savePath,
            'contract_signed_pdf_path' => $savePath,
        ]);

        return Response::make($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Contrat-signe-'.$client->id.'.pdf"',
        ]);
    }

    return back()->with('error', "Contrat signé introuvable pour ce client.");
}


}