<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Client;

class YousignWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->json()->all();

        // ✅ Yousign uses "event_name"
        $event = (string) data_get($payload, 'event_name', '');

        // IDs exactly where Yousign puts them
        $srId  = data_get($payload, 'data.signature_request.id');                       // signature request id
        $extId = data_get($payload, 'data.signature_request.external_id');              // your client id if you set it
        $docId = data_get($payload, 'data.signature_request.documents.0.id');           // first document id

        Log::info('YS webhook IN', compact('event','srId','extId','docId'));

        // Find the client (external_id → SR id → doc id)
        $client = null;

        if (!empty($extId)) {
            $client = Client::where('id', $extId)->first();
        }
        if (!$client && !empty($srId)) {
            $client = Client::where('yousign_signature_request_id', $srId)->first();
        }
        if (!$client && !empty($docId)) {
            $client = Client::where('yousign_document_id', $docId)->first();
        }

        if (!$client) {
            Log::warning('YS webhook: no matching client', compact('event','srId','extId','docId'));
            return response()->json(['ok' => true]);
        }

        $updates = [
            // keep what we learn
            'yousign_signature_request_id' => $client->yousign_signature_request_id ?: ($srId ?: null),
            'yousign_document_id'          => $client->yousign_document_id ?: ($docId ?: null),
        ];

        switch ($event) {
            case 'signature_request.activated':
                $updates += ['statut_gsauto' => 'activated', 'statut_signature' => 0, 'statut_termine' => 0];
                break;

            case 'signer.link_opened':
                $updates += ['statut_gsauto' => 'viewed'];
                break;

            case 'signer.done':
                // at least one signer finished
                $updates += ['statut_gsauto' => 'partially_signed', 'statut_signature' => 1];
                break;

            case 'signature_request.done':
                // whole request is done
                $updates += ['statut_gsauto' => 'signed', 'statut_signature' => 1, 'statut_termine' => 1, 'signed_at' => now()];
                break;

            default:
                $updates += ['statut_gsauto' => $event ?: 'unknown'];
                break;
        }

        $client->update($updates);

        Log::info('YS webhook UPDATED', ['client_id' => $client->id, 'updates' => $updates]);

        return response()->json(['ok' => true]);
    }
}