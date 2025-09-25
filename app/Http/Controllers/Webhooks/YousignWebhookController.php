<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\YousignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class YousignWebhookController extends Controller
{
    public function handle(Request $request, YousignService $ys)
    {
        // 1) Verify signature (RAW body – no timestamp)
        $payload   = $request->getContent();
        $headerSig = $request->header('X-Yousign-Signature-256');     // exact header name
        $secret    = (string) config('services.yousign.webhook_secret'); // raw secret (NO "sha256=" prefix)

        if (!$headerSig || !$secret) {
            return response('Missing header/secret', 400);
        }

        $computed = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        if (! hash_equals($computed, $headerSig)) {
            Log::warning('Yousign webhook signature mismatch', [
                'received' => $headerSig,
                'computed' => $computed,
            ]);
            return response('Invalid signature', 403);
        }

        // 2) Parse event + SR id
        $event = $request->input('event');
        $data  = $request->input('data', []);

        $srId = data_get($data, 'signature_request.id')
             ?: data_get($data, 'signature_request_id')
             ?: data_get($data, 'id');

        if (!$event || !$srId) {
            return response('Invalid payload', 400);
        }

        // 3) Find our Client by stored SR id (support both column names)
        $client = Client::where('yousign_signature_request_id', $srId)
                        ->orWhere('yousign_request_id', $srId)
                        ->first();

        if (! $client) {
            Log::info('Yousign webhook for unknown SR', compact('event','srId'));
            return response()->noContent();
        }

        // 4) Map events
        if ($event === 'signature_request.activated') {
            $client->update(['statut_gsauto' => 'sent']);
        }

        if ($event === 'signer.link_opened') {
            $client->update(['statut_gsauto' => 'viewed']);
        }

        if ($event === 'signer.done' || $event === 'signature_request.done') {
            try {
                // download merged signed PDF (Yousign v3 “documents/download”)
                $pdf = $ys->downloadSignedDocuments($srId);

                $dir  = "contracts/{$client->id}";
                $file = "contract-signed.pdf";
                Storage::disk('public')->makeDirectory($dir);
                Storage::disk('public')->put("{$dir}/{$file}", $pdf);

                // tolerate either column name
                if (Schema::hasColumn('clients', 'contract_signed_pdf_path')) {
                    $client->contract_signed_pdf_path = "{$dir}/{$file}";
                } else {
                    $client->signed_pdf_path = "{$dir}/{$file}";
                }

                $client->statut_gsauto = 'signed';
                $client->signed_at     = now();
                $client->save();
            } catch (\Throwable $e) {
                Log::error('Failed to download signed PDF', [
                    'srId'  => $srId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->noContent(); // 204
    }
}