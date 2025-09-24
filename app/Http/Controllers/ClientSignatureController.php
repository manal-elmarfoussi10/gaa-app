<?php
// app/Http/Controllers/ClientSignatureController.php
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientSignatureController extends Controller
{
    public function send(Request $request, Client $client)
    {
        // TODO: call your Yousign service here
        app()->make(\App\Services\YousignService::class)->sendContract($client);

        $client->update(['statut_gsauto' => 'sent']);
        return back()->with('success', 'Document envoyé au client pour signature.');
        
    }
}
