<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class YousignService
{
    protected string $base;
    protected string $token;

    public function __construct()
    {
        $this->base  = (string) config('services.yousign.base_url', 'https://api-sandbox.yousign.app/v3');
        $this->token = (string) config('services.yousign.api_key');

        if (!$this->token) {
            throw new \RuntimeException('YOUSIGN_API_KEY is missing.');
        }
    }

    protected function client(): PendingRequest
    {
        return Http::withToken($this->token)->baseUrl($this->base);
    }

    // 1) Create a signature request
    public function createSignatureRequest(string $name, string $deliveryMode = 'email'): array
    {
        // If you plan to embed in an iframe, use 'none' per docs; email is fine for email delivery.
        return $this->client()
            ->post('/signature_requests', [
                'name'         => $name,
                'delivery_mode'=> $deliveryMode,   // 'email' or 'none'
                'timezone'     => config('services.yousign.timezone', 'Europe/Paris'),
            ])
            ->throw()
            ->json();
    }

    // 2) Upload a PDF to the SR (multipart/form-data)
    public function uploadDocument(string $signatureRequestId, string $absolutePath, bool $parseAnchors = true): array
    {
        $filename = basename($absolutePath);

        return $this->client()
            ->asMultipart()
            ->attach('file', file_get_contents($absolutePath), $filename)
            ->attach('nature', 'signable_document')
            ->attach('parse_anchors', $parseAnchors ? 'true' : 'false')
            ->post("/signature_requests/{$signatureRequestId}/documents")
            ->throw()
            ->json();
    }

    // 3) Add a signer (use fields if you don’t use smart anchors)
    public function addSigner(string $signatureRequestId, array $payload): array
    {
        // payload example in controller below
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/signers", $payload)
            ->throw()
            ->json();
    }

    // 4) Activate signature request
    public function activate(string $signatureRequestId): array
    {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/activate")
            ->throw()
            ->json();
    }
}