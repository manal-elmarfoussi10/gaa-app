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
        // Verify signature against RAW body
        $raw      = $request->getContent(); // RAW payload
        $received = $request->header('X-Yousign-Signature-256'); // e.g. "sha256-abcdef..."
        $secret   = (string) config('services.yousign.webhook_secret'); // RAW secret from ENV

        if (!$received || !$secret) {
            return response('Missing header/secret', 400);
        }

        $digest   = hash_hmac('sha256', $raw, $secret);
        $computed = 'sha256-' . $digest; // <-- IMPORTANT: hyphen, not equals

        if (!hash_equals($computed, $received)) {
            Log::warning('Yousign webhook signature mismatch', [
                'received' => $received,
                'computed' => $computed,
            ]);
            return response('Invalid signature', 403);
        }

        // Parse event + id
        $event = $request->input('event');
        $data  = $request->input('data', []);
        $srId  = data_get($data, 'signature_request.id')
              ?: data_get($data, 'signature_request_id')
              ?: data_get($data, 'id');

        if (!$event || !$srId) {
            return response('Invalid payload', 400);
        }

        // Find the client (support both columns)
        $client = Client::where('yousign_signature_request_id', $srId)
                        ->orWhere('yousign_request_id', $srId)
                        ->first();

        if (!$client) {
            Log::info('Webhook for unknown client', compact('event','srId'));
            return response()->noContent();
        }

        // Map status
        if ($event === 'signature_request.activated') {
            $client->update(['statut_gsauto' => 'sent']);
        } elseif ($event === 'signer.link_opened') {
            $client->update(['statut_gsauto' => 'viewed']);
        } elseif ($event === 'signer.done' || $event === 'signature_request.done') {
            try {
                $pdf = $ys->downloadSignedDocuments($srId);

                $dir  = "contracts/{$client->id}";
                $file = "contract-signed.pdf";
                Storage::disk('public')->makeDirectory($dir);
                Storage::disk('public')->put("{$dir}/{$file}", $pdf);

                // Write to whichever column your UI reads
                if (Schema::hasColumn('clients', 'contract_signed_pdf_path')) {
                    $client->contract_signed_pdf_path = "{$dir}/{$file}";
                } else {
                    $client->signed_pdf_path = "{$dir}/{$file}";
                }

                $client->statut_gsauto = 'signed';
                $client->signed_at     = now();
                $client->save();
            } catch (\Throwable $e) {
                Log::error('Signed PDF download failed', ['srId' => $srId, 'err' => $e->getMessage()]);
            }
        }

        return response()->noContent(); // 204
    }
}