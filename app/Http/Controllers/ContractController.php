<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    // --- PDF build & store (called by your button) ---
    public function generate(Request $request, Client $client)
    {
        $pdf = Pdf::loadView('contracts.client', ['client' => $client])->setPaper('a4');

        $dir      = "contracts/{$client->id}";
        $filename = 'contract.pdf';
        $path     = "{$dir}/{$filename}";

        Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put($path, $pdf->output());

        $client->contract_pdf_path = $path;
        $client->statut_gsauto     = $client->statut_gsauto ?: 'draft';
        $client->save();

        return back()->with('success', 'Contrat généré.')->with('open_signature', true);
    }

    public function download(Client $client)
    {
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            return back()->with('error', 'Aucun contrat généré pour ce client.');
        }
        return Storage::disk('public')->download($client->contract_pdf_path, "Contrat-{$client->id}.pdf");
    }

    public function downloadSigned(Client $client)
    {
        if (!$client->contract_signed_pdf_path || !Storage::disk('public')->exists($client->contract_signed_pdf_path)) {
            return back()->with('error', 'Aucun contrat signé disponible pour ce client.');
        }
        return Storage::disk('public')->download($client->contract_signed_pdf_path, "Contrat-Signe-{$client->id}.pdf");
    }

    // --- Send for e-signature (uses service above) ---
    public function send(Request $request, Client $client, YousignService $ys)
    {
        // Ensure a PDF exists
        if (!$client->contract_pdf_path || !Storage::disk('public')->exists($client->contract_pdf_path)) {
            // auto-generate if missing
            $this->generate($request, $client);
            $client->refresh();
        }

        $absPath = Storage::disk('public')->path($client->contract_pdf_path);

        // 1) Create SR
        $sr = $ys->createSignatureRequest("Contrat #{$client->id} - {$client->nom}", 'email');

        // 2) Upload document (use smart anchors in your template or later give manual fields)
        $doc = $ys->uploadDocument($sr['id'], $absPath, true);

        // 3) Add signer (no manual fields if you used smart anchors)
        $payload = [
            'info' => [
                'first_name'   => $client->prenom ?? $client->nom,
                'last_name'    => $client->nom ?? '—',
                'email'        => $client->email,
                'phone_number' => $client->telephone ?? null,   // optional
                'locale'       => config('services.yousign.locale', 'fr'),
            ],
            'signature_level'               => 'electronic_signature',
            'signature_authentication_mode' => 'otp_email',   // or 'no_otp' in sandbox
            // If you did NOT use smart anchors, add fields:
            // 'fields' => [[ 'document_id' => $doc['id'], 'type' => 'signature', 'page' => 1, 'width' => 180, 'x' => 400, 'y' => 650 ]],
        ];
        $ys->addSigner($sr['id'], $payload);

        // 4) Activate
        $ys->activate($sr['id']);

        // persist ids + status
        $client->yousign_request_id = $sr['id'];
        $client->statut_gsauto      = 'sent';
        $client->save();

        return back()->with('success', 'Document envoyé au client pour signature.')->with('open_signature', true);
    }

    public function resend(Request $request, Client $client, YousignService $ys)
    {
        // In v3, to “resend”, create a new Signature Request (simplest) or manage reminders via API if needed.
        return $this->send($request, $client, $ys);
    }
}