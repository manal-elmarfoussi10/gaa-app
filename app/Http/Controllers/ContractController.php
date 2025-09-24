<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    /** First send */
    public function send(Client $client, YousignService $ys)
    {
        if (!filter_var($client->email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', "Email client manquant ou invalide.");
        }

        // 1) Generate the PDF from Blade
        $client->load('company');
        $pdf = Pdf::loadView('contracts.gsauto_contract', ['client' => $client]);
        Storage::makeDirectory('contracts');
        $path = storage_path("app/contracts/contrat_{$client->id}.pdf");
        file_put_contents($path, $pdf->output());

        // 2) Yousign: create procedure
        $proc = $ys->createProcedure('Contrat GS Auto – '.$client->nomComplet);
        if (empty($proc['id'])) {
            return back()->with('error', 'Yousign: échec de création de la procédure.');
        }

        // 3) Upload file
        $file = $ys->uploadFile($proc['id'], $path, "contrat_{$client->id}.pdf");

        // 4) Add signer
        $ys->addRecipient($proc['id'], [
            'firstname' => $client->prenom ?: 'Client',
            'lastname'  => $client->nom_assure ?: 'Assuré',
            'email'     => $client->email,
            'phone'     => $client->telephone,
        ]);

        // 5) Start
        $ys->startProcedure($proc['id']);

        // 6) Save refs
        $client->update([
            'statut_gsauto'        => 'sent',
            'yousign_procedure_id' => $proc['id'],
            'yousign_file_id'      => $file['id'] ?? null,
        ]);

        return back()->with('success', 'Contrat envoyé pour signature.')->with('open_signature', true);
    }

    /** Optional: resend (new procedure) */
    public function resend(Client $client, YousignService $ys)
    {
        // You can regenerate a fresh contract & procedure if previous expired/failed
        return $this->send($client, $ys);
    }
}