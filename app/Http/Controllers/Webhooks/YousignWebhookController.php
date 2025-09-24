<?php

namespace App\Http\Controllers\Webhooks;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class YousignWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // TEMP: just log; add verification + status updates later
        Log::channel('single')->info('Yousign webhook', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        return response('ok', 200);
    }
}