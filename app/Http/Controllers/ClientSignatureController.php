<?php
// app/Http/Controllers/ClientSignatureController.php
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Services\YousignService;

class ClientSignatureController extends Controller
{
    public function __construct(private YousignService $yousign) {}

    public function send(Request $request, Client $client)
    {
        $this->yousign->sendContract($client);
        $client->update(['statut_gsauto' => 'sent']);

        return back()->with('success', 'Document envoyé au client pour signature.');
    }

    public function resend(Request $request, Client $client)
    {
        // Simple strategy: re-create & re-send
        $this->yousign->sendContract($client);
        return back()->with('success', 'Invitation renvoyée.');
    }
}
