<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YousignWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // log everything so we can inspect safely
        Log::info('Yousign webhook TEST', [
            'event' => $request->input('event'),
            'payload' => $request->all(),
        ]);

        // Always return 200 so Yousign stops retrying
        return response()->json(['ok' => true]);
    }
}
