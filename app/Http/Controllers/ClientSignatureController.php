<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientSignatureController extends Controller
{
    public function send(Request $request, Client $client, \App\Services\YousignService $yousign)
{
    // Ensure a contract exists
    if (!$client->contract_pdf_path || !\Storage::exists($client->contract_pdf_path)) {
        // Build it now (reusing the same code as ContractController)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('contracts.contract', [
            'client' => $client,
            'today'  => now(),
        ])->setPaper('a4');

        $relative = "contracts/{$client->id}.pdf";
        \Storage::put($relative, $pdf->output());
        $client->contract_pdf_path = $relative;
        $client->save();
    }

    $absolute = \Storage::path($client->contract_pdf_path);

    $resp = $yousign->sendContract($absolute, [
        'email'      => $client->email ?: 'demo@example.com',
        'first_name' => $client->prenom ?: 'Prénom',
        'last_name'  => $client->nom_assure ?: 'Nom',
    ], name: "Contrat {$client->prenom} {$client->nom_assure}");

    $client->update([
        'statut_gsauto'                 => 'sent',
        'yousign_signature_request_id'  => $resp['signature_request']['id'] ?? null,
        'yousign_document_id'           => $resp['document']['id'] ?? null,
    ]);

    return back()->with('success', 'Document envoyé au client pour signature.')->with('open_signature', true);
}

    public function resend(Request $request, Client $client, YousignService $yousign)
    {
        // If you stored $client->yousign_signature_request you could re-activate or remind.
        // Yousign v3 doesn’t “resend” via a specific endpoint; usually you re-activate or send reminders.
        // For demo, we simply say OK.
        return back()->with('success', 'Rappel envoyé (simulation).');
    }
}