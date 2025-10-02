<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Client;
use App\Services\YousignService;

class YousignWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->json()->all();

        // ✅ Yousign uses "event_name" (not "event")
        $event = (string) data_get($payload, 'event_name', '');

        // IDs exactly where Yousign puts them (per your screenshots)
        $srId  = data_get($payload, 'data.signature_request.id');                       // signature request id
        $extId = data_get($payload, 'data.signature_request.external_id');              // your client id if you set it on creation
        $docId = data_get($payload, 'data.signature_request.documents.0.id');           // first document id

        Log::info('YS webhook IN', compact('event', 'srId', 'extId', 'docId'));

        // Find the client (external_id → SR id → doc id)
        $client = null;

        if (!empty($extId)) {
            $client = Client::where('id', $extId)->first();
        }
        if (!$client && !empty($srId)) {
            $client = Client::where('yousign_signature_request_id', $srId)->first();
        }
        if (!$client && !empty($docId)) {
            $client = Client::where('yousign_document_id', $docId)->first();
        }

        if (!$client) {
            Log::warning('YS webhook: no matching client', compact('event','srId','extId','docId'));
            return response()->json(['ok' => true]);
        }

        // Base updates: keep what we learn (don’t overwrite existing non-null values)
        $updates = [
            'yousign_signature_request_id' => $client->yousign_signature_request_id ?: ($srId ?: null),
            'yousign_document_id'          => $client->yousign_document_id ?: ($docId ?: null),
        ];

        switch ($event) {
            case 'signature_request.activated':
                $updates += ['statut_gsauto' => 'activated', 'statut_signature' => 0, 'statut_termine' => 0];
                break;

            case 'signer.link_opened':
                $updates += ['statut_gsauto' => 'viewed'];
                break;

            case 'signer.done':
                // At least one signer finished
                $updates += ['statut_gsauto' => 'partially_signed', 'statut_signature' => 1];
                break;

            case 'signature_request.done':
                // Whole request is done ⇒ mark signed and attempt to save the signed PDF
                $updates += [
                    'statut_gsauto'  => 'signed',
                    'statut_signature'=> 1,
                    'statut_termine' => 1,
                    'signed_at'      => now(),
                ];

                // Try to fetch and persist the signed PDF (if we have both ids)
                try {
                    $finalSrId  = $srId ?: $client->yousign_signature_request_id;
                    $finalDocId = $docId ?: $client->yousign_document_id;

                    if ($finalSrId && $finalDocId) {
                        /** @var \App\Services\YousignService $ys */
                        $ys  = app(YousignService::class);
                        $pdf = $ys->downloadSignedDocument($finalSrId, $finalDocId); // returns raw bytes

                        // Store under public disk so we can expose it in UI
                        $savePath = "contracts/{$client->id}/contract-signed.pdf";
                        Storage::disk('public')->put($savePath, $pdf);

                        // Update both columns so existing blades light up
                        $updates['signed_pdf_path']          = $savePath; // legacy
                        $updates['contract_signed_pdf_path'] = $savePath; // new accessor target
                    } else {
                        Log::warning('YS webhook: done without enough IDs to download PDF', [
                            'client_id' => $client->id, 'srId' => $finalSrId, 'docId' => $finalDocId
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('YS webhook: unable to save signed PDF', [
                        'client_id' => $client->id,
                        'srId'      => $srId,
                        'docId'     => $docId,
                        'error'     => $e->getMessage(),
                    ]);
                }

                break;

            default:
                // Keep a trace of other events in statut_gsauto
                $updates += ['statut_gsauto' => $event ?: 'unknown'];
                break;
        }

        $client->update($updates);

        Log::info('YS webhook UPDATED', ['client_id' => $client->id, 'updates' => $updates]);

        return response()->json(['ok' => true]);
    }
}