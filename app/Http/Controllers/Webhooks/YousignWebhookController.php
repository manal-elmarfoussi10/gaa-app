<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YousignWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log once so we can see it hit on the server
        Log::info('Yousign webhook hit', [
            'ip'     => $request->ip(),
            'event'  => $request->input('event'),
            'header' => $request->header('X-Yousign-Signature-256'),
        ]);

        // IMPORTANT: Always answer quick with 204
        return response()->noContent();
    }
}