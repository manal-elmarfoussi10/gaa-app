<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YousignWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $signature = $request->header('X-Yousign-Signature');
        $secret    = config('yousign.webhook_secret');
        $computed  = hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($computed, (string) $signature)) {
            return response()->json(['ok'=>false], 401);
        }

        $payload = $request->all();
        $event = $payload['eventName'] ?? null;
        $procedureId = data_get($payload, 'procedure.id');

        $client = Client::where('yousign_procedure_id', $procedureId)->first();
        if (!$client) return response()->json(['ok'=>true]);

        $map = [
            'procedure.started' => 'sent',
            'procedure.viewed'  => 'viewed',
            'procedure.finished'=> 'signed',
            'member.finished'   => 'signed',
            'procedure.refused' => 'failed',
        ];

        if (isset($map[$event])) {
            $client->statut_gsauto = $map[$event];
            if ($map[$event] === 'signed') $client->signed_at = now();
            $client->save();
        }

        return response()->json(['ok'=>true]);
    }
}