<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YousignWebhookController extends Controller
{
    public function handle(Request $request, YousignService $ys)
    {
        // 1) Verify signature (use RAW body, no timestamp)
        $payload       = $request->getContent(); // RAW body
        $headerSig     = $request->header('X-Yousign-Signature-256'); // exact header name
        $secret        = config('services.yousign.webhook_secret');   // raw secret, no "sha256="

        if (!$headerSig || !$secret) {
            return response('Missing header/secret', 400);
        }

        $computed = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        if (! hash_equals($computed, $headerSig)) {
            // Optional: Log once while testing
            Log::warning('Yousign webhook signature mismatch', [
                'received' => $headerSig,
                'computed' => $computed,
            ]);
            return response('Invalid signature', 403);
        }

        // 2) Parse event + ids
        $event = $request->input('event');
        $data  = $request->input('data', []);

        // SR id may appear in a few shapes depending on event
        $srId = data_get($data, 'signature_request.id')
             ?: data_get($data, 'signature_request_id')
             ?: data_get($data, 'id');

        if (!$event || !$srId) {
            return response('Invalid payload', 400);
        }

        // 3) Find client by our stored request id (support both column names)
        $client = Client::where('yousign_request_id', $srId)
                        ->orWhere('yousign_signature_request_id', $srId)
                        ->first();

        if (! $client) {
            Log::info('Yousign webhook for unknown SR', ['event' => $event, 'srId' => $srId]);
            return response()->noContent();
        }

        // 4) Map events
        if ($event === 'signature_request.activated') {
            $client->statut_gsauto = 'sent';
            $client->save();
        }

        if ($event === 'signer.link_opened') {
            $client->statut_gsauto = 'viewed';
            $client->save();
        }

        if ($event === 'signer.done' || $event === 'signature_request.done') {
            try {
                $pdf = $ys->downloadSignedDocuments($srId);
                $dir  = "contracts/{$client->id}";
                $file = "contract-signed.pdf";
                \Storage::disk('public')->makeDirectory($dir);
                \Storage::disk('public')->put("{$dir}/{$file}", $pdf);

                // support either column name
                if (\Schema::hasColumn('clients', 'contract_signed_pdf_path')) {
                    $client->contract_signed_pdf_path = "{$dir}/{$file}";
                } else {
                    $client->signed_pdf_path = "{$dir}/{$file}";
                }
                $client->statut_gsauto = 'signed';
                $client->signed_at = now();
                $client->save();
            } catch (\Throwable $e) {
                Log::error('Failed to download signed PDF', ['srId' => $srId, 'err' => $e->getMessage()]);
            }
        }

        return response()->noContent(); // 204
    }
}