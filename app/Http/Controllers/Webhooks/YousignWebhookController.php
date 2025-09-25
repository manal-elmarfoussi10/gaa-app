<?php

namespace App\Http\Controllers;

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
        // --- Verify signature (spec requires X-Yousign-Signature-256) ---
        $rawBody   = $request->getContent(); // raw!
        $headerSig = $request->header('X-Yousign-Signature-256');
        $secret    = config('services.yousign.webhook_secret');

        if (!$headerSig || !$secret) {
            return response('Missing signature/secret', Response::HTTP_BAD_REQUEST);
        }

        $computed = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
        if (!hash_equals($computed, $headerSig)) {
            return response('Invalid signature', Response::HTTP_FORBIDDEN);
        }

        // --- Parse event & SR id ---
        $event = $request->input('event');
        $srId  = data_get($request->input('data'), 'signature_request.id')
              ?: data_get($request->input('data'), 'id')
              ?: data_get($request->all(), 'signature_request_id');

        if (!$event || !$srId) {
            return response('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

        // --- Find our client (we saved yousign_request_id when creating SR) ---
        $client = Client::where('yousign_request_id', $srId)->first();
        if (!$client) {
            Log::warning('[Yousign] client not found for SR', compact('event','srId'));
            return response()->noContent();
        }

        // --- Update status by event ---
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
                // optional: mark “partially signed” if there are multiple signers
                $client->statut_gsauto = 'viewed'; // or keep current; final will be done below
                $client->save();
                break;

            case 'signature_request.done':
                // final: everyone signed → download signed PDF
                try {
                    $pdf = $ys->downloadSignedDocuments($srId); // binary
                    $dir = "contracts/{$client->id}";
                    Storage::disk('public')->makeDirectory($dir);
                    Storage::disk('public')->put("$dir/contract-signed.pdf", $pdf);

                    $client->contract_signed_pdf_path = "$dir/contract-signed.pdf";
                    $client->statut_gsauto            = 'signed';
                    $client->signed_at                 = now();
                    $client->save();
                } catch (\Throwable $e) {
                    Log::error('[Yousign] downloadSignedDocuments failed', [
                        'srId' => $srId,
                        'error' => $e->getMessage(),
                    ]);
                }
                break;
        }

        return response()->noContent();
    }
}