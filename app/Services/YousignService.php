<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YousignService
{
    protected string $base;
    protected string $token;

    public function __construct()
    {
        $this->base  = (string) (config('services.yousign.base_url') ?: 'https://api-sandbox.yousign.app/v3');
        $this->token = (string)  config('services.yousign.api_key');

        if (!$this->token) {
            throw new \RuntimeException('YOUSIGN_API_KEY is missing.');
        }
    }

    protected function client()
    {
        return Http::withToken($this->token)->baseUrl($this->base);
    }

    /** 1) Create signature request */
    public function createSignatureRequest(string $name, string $delivery = 'email'): array
    {
        return $this->client()
            ->post('/signature_requests', [
                'name'         => $name,
                'delivery_mode'=> $delivery,     // "email" or "none"
                'timezone'     => 'Europe/Paris',
            ])
            ->throw()
            ->json();
    }

    /** 2) Upload a document */
    public function uploadDocument(string $signatureRequestId, string $absolutePath, bool $parseAnchors = false): array
    {
        return $this->client()
            ->asMultipart()
            ->post("/signature_requests/{$signatureRequestId}/documents", [
                // order matters in multipart
                ['name' => 'file',          'contents' => fopen($absolutePath, 'r'), 'filename' => basename($absolutePath)],
                ['name' => 'nature',        'contents' => 'signable_document'],
                ['name' => 'parse_anchors', 'contents' => $parseAnchors ? 'true' : 'false'],
            ])
            ->throw()
            ->json();
    }

    /**
     * 3) Add signer.
     * If you aren’t using anchors, pass a field (coords are example values; tune for your PDF).
     */
    public function addSigner(string $signatureRequestId, array $payload): array
    {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/signers", $payload)
            ->throw()
            ->json();
    }

    /** 4) Activate (send) the request */
    public function activate(string $signatureRequestId): array
    {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/activate")
            ->throw()
            ->json();
    }
}