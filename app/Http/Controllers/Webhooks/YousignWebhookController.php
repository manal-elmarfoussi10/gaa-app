<?php

// app/Http/Controllers/Webhooks/YousignWebhookController.php
namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YousignWebhookController extends Controller
{
    public function __invoke(Request $request, YousignService $ys)
    {
        $payload = $request->json()->all();
        Log::info('Yousign webhook', $payload);

        // (optional) verify the signature header against your YOUSIGN_WEBHOOK_SECRET
        // $request->header('X-Yousign-Signature') …

        // Yousign v3 typically sends an event type and the signature_request id
        $type = data_get($payload, 'type'); // e.g. "signature_request.completed"
        $srId = data_get($payload, 'data.id')            // some payloads
             ?? data_get($payload, 'signature_request')  // others
             ?? null;

        if (!$srId) {
            return response()->json(['ok' => true]); // nothing to do
        }

        $client = Client::where('yousign_request_id', $srId)->first();
        if (!$client) {
            return response()->json(['ok' => true]); // unknown SR, ignore
        }

        // Mark intermediate states if you like
        if (in_array($type, [
            'signature_request.activated',
            'signature_request.viewed',
            'signature_request.reminded',
        ], true)) {
            $client->update(['statut_gsauto' => 'sent']);
            return response()->json(['ok' => true]);
        }

        // Final: completed = signed by everyone
        if (in_array($type, ['signature_request.completed','signature_request.done'], true)) {

            // 1) store status
            $client->statut_gsauto = 'signed';
            $client->signed_at     = now();

            // 2) (optional) download the signed PDF and store it
            try {
                $bytes = $ys->downloadSignedPdf($srId); // add this in the service (below)
                $path  = "contracts/{$client->id}/contract-signed.pdf";
                Storage::disk('public')->put($path, $bytes);
                $client->contract_signed_pdf_path = $path;
            } catch (\Throwable $e) {
                Log::warning('Could not fetch signed PDF from Yousign', ['sr' => $srId, 'e' => $e->getMessage()]);
            }

            $client->save();
        }

        return response()->json(['ok' => true]);
    }
}