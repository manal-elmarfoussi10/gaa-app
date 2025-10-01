<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SignatureEvent;
use App\Models\Client;

class YousignWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Read JSON payload
        $payload = $request->json()->all();

        // Normalize common fields from payload (Yousign = data.signature_request.*)
        $event = data_get($payload, 'event');
        $srId  = data_get($payload, 'data.signature_request.id');
        $docId = data_get($payload, 'data.signature_request.documents.0.id');

        // 1) Persist the raw event (always)
        //    If your SignatureEvent model has `$casts = ['payload' => 'array'];`,
        //    you can save the array directly.
        $row = SignatureEvent::create([
            'client_id'           => null,                 // we attach it below if we find the client
            'event_name'          => $event ?? 'unknown',
            'yousign_request_id'  => $srId,
            'yousign_document_id' => $docId,
            'payload'             => $payload,            // json column; cast as array in the model
        ]);

        // 2) Try to link event to a client via the SR id
        $client = null;
        if ($srId) {
            $client = Client::where('yousign_signature_request_id', $srId)->first();
            if ($client) {
                $row->client_id = $client->id;
                $row->save();
            }
        }

        // 3) If the whole request is finished, mark the client as signed
        if ($event === 'signature_request.done' && $client) {
            $client->update([
                'statut_signature' => 1,       // your dashboard flag
                'signed_at'        => now(),
            ]);
            Log::info('Client marked as signed', ['client_id' => $client->id, 'sr_id' => $srId]);
        }

        // Debug log (handy during setup)
        Log::info('Yousign webhook received', [
            'event' => $event,
            'sr_id' => $srId,
            'doc_id'=> $docId,
            'ip'    => $request->ip(),
            'row_id'=> $row->id,
        ]);

        // Webhooks must be quick and quiet
        return response()->noContent(); // 204
    }
}