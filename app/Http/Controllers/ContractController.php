<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    /**
     * Génère le contrat PDF et le stocke dans storage/app/public/contracts/{id}/contract.pdf
     */
    public function generate(Request $request, Client $client)
    {
        // Charger la relation company pour l'affichage du contrat
        $client->loadMissing('company');

        // Générer le PDF depuis la vue Blade
        $pdf = Pdf::loadView('contracts.contract', [
            'client'  => $client,
            'company' => $client->company,
        ])->setPaper('a4');

        $dir      = "contracts/{$client->id}";
        $filename = 'contract.pdf';
        $path     = "{$dir}/{$filename}";

        // Créer le répertoire si besoin
        Storage::disk('public')->makeDirectory($dir);

        // Sauvegarder le PDF
        Storage::disk('public')->put($path, $pdf->output());

        // Mettre à jour le client
        $client->update([
            'contract_pdf_path' => $path,
            'statut_gsauto'     => $client->statut_gsauto ?: 'draft',
        ]);

        return back()->with('success', 'Contrat généré.')->with('open_signature', true);
    }

    /**
     * Télécharger le contrat brut (non signé)
     */
    public function download(Client $client)
    {
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat généré pour ce client.');
        }

        return Storage::disk('public')->download(
            $client->contract_pdf_path,
            "Contrat-{$client->id}.pdf"
        );
    }

    /**
     * Télécharger le contrat signé (si disponible)
     */
    public function downloadSigned(Client $client)
    {
        if (!$client->signed_pdf_path || !Storage::disk('public')->exists($client->signed_pdf_path)) {
            return back()->with('error', 'Aucun contrat signé disponible pour ce client.');
        }

        return Storage::disk('public')->download(
            $client->signed_pdf_path,
            "Contrat-Signe-{$client->id}.pdf"
        );
    }

    /**
     * Envoyer le contrat pour signature électronique via Yousign
     */
    public function send(Request $request, Client $client, YousignService $ys)
    {
        // Vérifier qu'un PDF existe, sinon le générer
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            $this->generate($request, $client);
            $client->refresh();
        }

        $absPath = Storage::disk('public')->path($client->contract_pdf_path);

        try {
            // 1) Créer une demande de signature
            $title = "Contrat #{$client->id} - {$client->nom_complet}";
            $sr = $ys->createSignatureRequest($title, 'email'); // retourne ['id' => ...]

            // 2) Uploader le document (⚠️ avecAnchors = false si pas d’ancres dans ton PDF)
            $doc = $ys->uploadDocument($sr['id'], $absPath, false);

            // 3) Ajouter le signataire
            $phone = $client->telephone;
            if ($phone && !preg_match('/^\+\d{6,15}$/', $phone)) {
                $phone = null; // Yousign exige format E.164
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
                'signature_authentication_mode' => 'otp_email', // ou 'no_otp' pour sandbox
                'fields' => [[
                    'document_id' => $doc['id'],
                    'type'        => 'signature',
                    'page'        => 1,
                    'x'           => 100,  // ajuster selon ton design
                    'y'           => 700,  // ajuster selon ton design
                    'width'       => 150,
                    'height'      => 40,
                ]],
            ];

            $ys->addSigner($sr['id'], $payload);

            // 4) Activer (envoi du mail au client)
            $ys->activate($sr['id']);

            // 5) Persister dans la DB
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
                'error'     => $e->getMessage()
            ]);
            return back()->with('error', "L'envoi vers Yousign a échoué : ".$e->getMessage());
        }
    }

    /**
     * Relancer le client (Yousign enverra un rappel)
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
                'error'     => $e->getMessage()
            ]);
            return back()->with('error', "La relance a échoué : ".$e->getMessage());
        }
    }
}