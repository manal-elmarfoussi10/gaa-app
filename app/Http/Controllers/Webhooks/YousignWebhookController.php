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
        $event   = (string) data_get($payload, 'event', '');

        Log::info('Yousign webhook IN', [
            'event'   => $event,
            'payload' => $payload,
        ]);

        // Try to identify the client
        $externalId = data_get($payload, 'data.external_id');
        $signatureRequestId = data_get($payload, 'data.id') 
                           ?? data_get($payload, 'data.signature_request.id');

        $documentId = data_get($payload, 'data.document.id');

        $query = Client::query();

        if ($externalId) {
            $query->where('id', $externalId);
        } elseif ($signatureRequestId) {
            $query->where('yousign_signature_request_id', $signatureRequestId);
        } else {
            Log::warning('Webhook without externalId or signatureRequestId');
            return response()->json(['ok' => true]);
        }

        $updates = [
            'yousign_signature_request_id' => $signatureRequestId,
            'yousign_document_id'          => $documentId,
        ];

        switch ($event) {
            case 'signature_request.activated':
                $updates['statut_gsauto']    = 'activated';
                $updates['statut_signature'] = 0;
                $updates['statut_termine']   = 0;
                break;

            case 'signer.done':
                $updates['statut_gsauto']    = 'partially_signed';
                $updates['statut_signature'] = 1;
                break;

            case 'signature_request.done':
                $updates['statut_gsauto']  = 'signed';
                $updates['statut_termine'] = 1;
                $updates['signed_at']      = now();
                break;

            default:
                $updates['statut_gsauto'] = $event;
        }

        $affected = $query->update($updates);

        Log::info('Yousign webhook UPDATE', [
            'rows' => $affected,
            'updates' => $updates,
        ]);

        return response()->json(['ok' => true]);
    }
}
