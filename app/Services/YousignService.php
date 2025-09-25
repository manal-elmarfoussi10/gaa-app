<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class YousignService
{
    private string $base;
    private string $token;

    public function __construct()
    {
        // Fallback to sandbox base URL if not set
        $this->base  = (string) (config('services.yousign.base_url') ?? 'https://api-sandbox.yousign.app/v3');
        $this->token = (string) config('services.yousign.api_key');

        if (!$this->token) {
            throw new RuntimeException('YOUSIGN_API_KEY is missing.');
        }
    }

    /** Base HTTP client */
    protected function client(): PendingRequest
    {
        return Http::withToken($this->token)
            ->baseUrl($this->base)
            ->acceptJson();
    }

    /** 1) Create a Signature Request */
    public function createSignatureRequest(
        string $name,
        string $deliveryMode = 'email',
        string $timezone = 'Europe/Paris'
    ): array {
        $payload = [
            'name'          => $name,
            'delivery_mode' => $deliveryMode,
            'timezone'      => $timezone,
        ];

        return $this->client()
            ->post('signature_requests', $payload)
            ->throw()
            ->json();
    }

    /** 2) Upload the document (PDF) to that request */
    public function uploadDocument(string $signatureRequestId, string $absolutePath, bool $parseAnchors = false): array
    {
        return $this->client()
            ->asMultipart()
            ->attach('file', fopen($absolutePath, 'r'), basename($absolutePath))
            ->post("signature_requests/{$signatureRequestId}/documents", [
                'nature'        => 'signable_document',
                // send as string booleans for multipart forms
                'parse_anchors' => $parseAnchors ? 'true' : 'false',
            ])
            ->throw()
            ->json();
    }

    /** 3) Add a signer to the request */
    public function addSigner(string $signatureRequestId, array $payload): array
    {
        // Yousign requires E.164 for phone_number. If invalid or empty -> remove it.
        if (isset($payload['info']['phone_number'])) {
            $phone = (string) $payload['info']['phone_number'];
            if ($phone === '' || !preg_match('/^\+\d{8,15}$/', $phone)) {
                unset($payload['info']['phone_number']);
            }
        }

        return $this->client()
            ->post("signature_requests/{$signatureRequestId}/signers", $payload)
            ->throw()
            ->json();
    }

    /** 4) Activate (send) the request */
    public function activate(string $signatureRequestId): array
    {
        return $this->client()
            ->post("signature_requests/{$signatureRequestId}/activate")
            ->throw()
            ->json();
    }
}