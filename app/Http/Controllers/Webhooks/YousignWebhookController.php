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

        // ---- Read identifiers EXACTLY like your payload shows ----
        $srId     = data_get($payload, 'data.signature_request.id');      // <-- present in your screenshot
        $extId    = data_get($payload, 'data.signature_request.external_id'); // often null in your tests
        $docId    = data_get($payload, 'data.documents.0.id');            // first document id if present

        Log::info('Yousign webhook IN', compact('event','srId','extId','docId'));

        // ---- Find the client robustly: by external_id, then SR id, then document id ----
        $q = Client::query();

        if (!empty($extId)) {
            $q->where('id', $extId);
        } elseif (!empty($srId)) {
            $q->where('yousign_signature_request_id', $srId);
        } elseif (!empty($docId)) {
            $q->where('yousign_document_id', $docId);
        } else {
            Log::warning('No linking key (external_id / signature_request_id / document_id).');
            return response()->json(['ok' => true]);
        }

        // ---- Build updates according to event ----
        $updates = [
            'yousign_signature_request_id' => $srId ?: $docId, // keep what we learn
            'yousign_document_id'          => $docId ?: null,
        ];

        switch ($event) {
            case 'signature_request.activated':
                $updates += ['statut_gsauto' => 'activated', 'statut_signature' => 0, 'statut_termine' => 0];
                break;

            case 'signer.done':
                $updates += ['statut_gsauto' => 'partially_signed', 'statut_signature' => 1];
                break;

            case 'signature_request.done':
                $updates += ['statut_gsauto' => 'signed', 'statut_termine' => 1, 'signed_at' => now()];
                break;

            default:
                $updates += ['statut_gsauto' => $event ?: 'unknown'];
        }

        $rows = $q->update($updates);
        Log::info('Yousign UPDATE', ['rows' => $rows, 'updates' => $updates]);

        return response()->json(['ok' => true]);
    }
}
