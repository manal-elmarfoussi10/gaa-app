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

        // Yousign sends "event_name", not "event"
        $eventName = (string) data_get($payload, 'event_name', '');

        // Identifiers inside data.signature_request
        $srId  = data_get($payload, 'data.signature_request.id');
        $extId = data_get($payload, 'data.signature_request.external_id'); // often null if you don’t set it
        $docId = data_get($payload, 'data.signature_request.documents.0.id'); // <- correct path

        Log::info('Yousign webhook IN', compact('eventName','srId','extId','docId'));

        // Find the client: prefer external_id, then SR id, then document id
        $q = Client::query();
        if (!empty($extId)) {
            $q->where('id', $extId);
        } elseif (!empty($srId)) {
            $q->where('yousign_signature_request_id', $srId);
        } elseif (!empty($docId)) {
            $q->where('yousign_document_id', $docId);
        } else {
            Log::warning('Webhook: no linking key (external_id / sr_id / doc_id). Payload skipped.');
            return response()->json(['ok' => true]);
        }

        // Always persist what we (may newly) learn
        $updates = array_filter([
            'yousign_signature_request_id' => $srId,
            'yousign_document_id'          => $docId,
        ]);

        // Map Yousign events to our status fields
        switch ($eventName) {
            case 'signature_request.activated':   // request opened/sent
                $updates += [
                    'statut_gsauto'     => 'activated',
                    'statut_signature'  => 0,
                    'statut_termine'    => 0,
                ];
                break;

            case 'signer.done':                    // at least one signer finished
                $updates += [
                    'statut_gsauto'     => 'partially_signed',
                    'statut_signature'  => 1,
                ];
                break;

            case 'signature_request.done':        // all signers done
                $updates += [
                    'statut_gsauto'     => 'signed',
                    'statut_termine'    => 1,
                    'signed_at'         => now(),
                ];
                break;

            case 'signer.link_opened':
                $updates += ['statut_gsauto' => 'viewed'];
                break;

            default:
                // Keep a trace of unknown events (handy in dev)
                $updates += ['statut_gsauto' => $eventName ?: 'unknown'];
        }

        $rows = $q->update($updates);
        Log::info('Yousign webhook UPDATE', ['rows' => $rows, 'updates' => $updates]);

        return response()->json(['ok' => true]);
    }
}