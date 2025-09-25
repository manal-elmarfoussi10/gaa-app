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
        // 0) Log event id for debugging
        $event = $request->input('event');
        Log::info('[Yousign] webhook received', [
            'event' => $event,
            'sig_header' => $request->header('X-Yousign-Signature-256'),
        ]);

        // 1) Verify signature exactly like Yousign docs
        $payload   = $request->getContent(); // raw body
        $headerSig = $request->header('X-Yousign-Signature-256');
        $secret    = (string) config('services.yousign.webhook_secret');

        if (!$headerSig || !$secret) {
            Log::warning('[Yousign] missing signature header or secret');
            return response('Missing', Response::HTTP_BAD_REQUEST);
        }

        $computed = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($headerSig, $computed)) {
            Log::warning('[Yousign] invalid signature', [
                'expected' => $computed,
                'got'      => $headerSig,
            ]);
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        // 2) Extract signature request id from payload
        $data = $request->input('data', []);
        $srId = data_get($data, 'id')
             ?: data_get($data, 'signature_request.id')
             ?: data_get($data, 'signature_request_id');

        if (!$event || !$srId) {
            Log::warning('[Yousign] invalid payload (no event or srId)', ['body' => $request->all()]);
            return response('Bad Request', Response::HTTP_BAD_REQUEST);
        }

        // 3) Find our Client (we stored yousign_request_id on send)
        $client = Client::where('yousign_request_id', $srId)->first();
        if (!$client) {
            Log::warning('[Yousign] client not found for sr', ['srId' => $srId]);
            return response()->noContent();
        }

        // 4) Map events to status + handle signed download
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
                try {
                    $pdfBinary = $ys->downloadSignedDocuments($srId);
                    $dir  = "contracts/{$client->id}";
                    $file = "contract-signed.pdf";
                    Storage::disk('public')->makeDirectory($dir);
                    Storage::disk('public')->put("{$dir}/{$file}", $pdfBinary);

                    $client->contract_signed_pdf_path = "{$dir}/{$file}";
                    $client->statut_gsauto            = 'signed';
                    $client->signed_at                = now();
                    $client->save();
                } catch (\Throwable $e) {
                    Log::error('[Yousign] download signed failed', [
                        'srId' => $srId,
                        'error'=> $e->getMessage(),
                    ]);
                }
                break;

            default:
                // ignore others
                break;
        }

        // 5) Always 204 quickly
        return response()->noContent();
    }
}