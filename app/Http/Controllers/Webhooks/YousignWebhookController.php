<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Client; // <-- we will update the clients table

class YousignWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Read JSON payload
        $payload = $request->json()->all();
        $event   = $payload['event'] ?? null;

        // Log once (helpful while testing)
        Log::info('Yousign webhook', [
            'event' => $event,
            'sr_id' => data_get($payload, 'data.signature_request.id'),
            'ip'    => $request->ip(),
        ]);

        // Only when ALL signers have finished
        if ($event === 'signature_request.done') {
            $srId = data_get($payload, 'data.signature_request.id');

            if ($srId) {
                // Find the client that has this Yousign signature request id
                $client = Client::where('yousign_signature_request_id', $srId)->first();

                if ($client) {
                    // Flip status used by your dashboard
                    $client->statut_signature = 1;    // 1 = signed/done
                    $client->signed_at        = now(); // record time
                    $client->save();

                    Log::info('Client marked as signed', ['client_id' => $client->id]);
                } else {
                    Log::warning('Client not found for Yousign SR', ['sr_id' => $srId]);
                }
            }
        }

        // Always respond quickly so Yousign is happy
        return response()->noContent(); // 204
    }
}