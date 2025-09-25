<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YousignWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // TEMP: log and accept everything so we can confirm end-to-end
        Log::info('Yousign webhook hit', [
            'headers' => $request->headers->all(),
            'body'    => $request->all(),
        ]);

        // return 204 No Content as recommended for webhooks
        return response()->noContent();
    }
}