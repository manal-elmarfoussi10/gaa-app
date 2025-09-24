<?php
// app/Http/Controllers/ClientSignatureController.php
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientSignatureController extends Controller
{
    public function send(Request $request, Client $client)
    {
        app(\App\Services\YousignService::class)->sendContract($client);
        $client->update(['statut_gsauto' => 'sent']);
        return back()->with('success', 'Document envoyé au client pour signature.');
    }

    public function resend(Request $request, Client $client)
    {
        app(\App\Services\YousignService::class)->resend($client);
        return back()->with('success', 'Document renvoyé au client.');
    }
}
