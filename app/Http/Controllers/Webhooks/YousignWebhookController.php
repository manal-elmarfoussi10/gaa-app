<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class YousignWebhookController extends Controller
{
    public function handle(Request $request, YousignService $ys)
    {
        // 1) Verify signature (required by Yousign)
        $payload   = $request->getContent();
        $timestamp = $request->header('X-Yousign-Timestamp');
        $signature = $request->header('X-Yousign-Signature-256');

        if (!$timestamp || !$signature) {
            return response('Missing headers', Response::HTTP_BAD_REQUEST);
        }

        // Optional replay protection (5 min)
        if (abs(time() - (int)$timestamp) > 300) {
            return response('Stale timestamp', Response::HTTP_BAD_REQUEST);
        }

        $secret = config('services.yousign.webhook_secret');
        $signed = $timestamp.'.'.$payload;
        $computed = 'sha256='.hash_hmac('sha256', $signed, $secret);

        // constant-time compare
        if (! hash_equals($computed, $signature)) {
            return response('Invalid signature', Response::HTTP_FORBIDDEN);
        }

        // 2) Parse event
        $event = $request->input('event');
        $data  = $request->input('data', []);
        $srId  = data_get($data, 'id')             // some events carry id at root
              ?: data_get($data, 'signature_request.id')
              ?: data_get($data, 'signature_request_id');

        if (!$event || !$srId) {
            return response('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

        // 3) Find our Client. We saved yousign_request_id when sending.
        /** @var Client|null $client */
        $client = Client::where('yousign_request_id', $srId)->first();

        if (! $client) {
            // Fallback: try to map by metadata if you set SR metadata, or just log.
            Log::warning('Yousign webhook: client not found', compact('event','srId'));
            return response()->noContent();
        }

        // 4) Map events to statuses
        switch ($event) {
            case 'signature_request.activated':
                $client->statut_gsauto = 'sent';
                $client->save();
                break;

            case 'signer.link_opened':
                $client->statut_gsauto = 'viewed';
                $client->save();
                break;

            case 'signer.done':
            case 'signature_request.done':
                // Download signed PDF and store it
                try {
                    $pdfBinary = $ys->downloadSignedDocuments($srId);

                    $dir  = "contracts/{$client->id}";
                    $file = "contract-signed.pdf";
                    Storage::disk('public')->makeDirectory($dir);
                    Storage::disk('public')->put("{$dir}/{$file}", $pdfBinary);

                    $client->contract_signed_pdf_path = "{$dir}/{$file}";
                    $client->statut_gsauto = 'signed';
                    $client->signed_at = now();
                    $client->save();
                } catch (\Throwable $e) {
                    Log::error('Failed to download signed doc', [
                        'srId' => $srId,
                        'error' => $e->getMessage(),
                    ]);
                }
                break;

            default:
                // no-op for other events
                break;
        }

        // 5) Always answer fast with 204 so Yousign doesn't retry
        return response()->noContent();
    }
}