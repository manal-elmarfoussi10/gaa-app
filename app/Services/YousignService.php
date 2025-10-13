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
        return Http::withToken($this->token)->baseUrl(rtrim($this->base, '/'));
    }

    /** 1) Create signature request (optionally pass ['external_id' => '...']) */
    public function createSignatureRequest(string $name, string $delivery = 'email', array $extra = []): array
    {
        $payload = array_merge([
            'name'          => $name,
            'delivery_mode' => $delivery,     // "email" or "none"
            'timezone'      => 'Europe/Paris',
        ], $extra);

        return $this->client()
            ->post('/signature_requests', $payload)
            ->throw()
            ->json();
    }

    /** 2) Upload a document to the signature request */
    public function uploadDocument(string $signatureRequestId, string $absolutePath, bool $parseAnchors = false): array
    {
        return $this->client()
            ->asMultipart()
            ->post("/signature_requests/{$signatureRequestId}/documents", [
                ['name' => 'file',          'contents' => fopen($absolutePath, 'r'), 'filename' => basename($absolutePath)],
                ['name' => 'nature',        'contents' => 'signable_document'],
                ['name' => 'parse_anchors', 'contents' => $parseAnchors ? 'true' : 'false'],
            ])
            ->throw()
            ->json();
    }

    /** 3) Add a signer (payload contains info, fields, etc.) */
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

    /** Helper: fetch a signature request (status, docs, etc.) */
    public function getSignatureRequest(string $id): array
    {
        return $this->client()
            ->get("/signature_requests/{$id}")
            ->throw()
            ->json();
    }

    /**
     * 5) Download the *signed* document (raw PDF bytes).
     *    This is the endpoint your webhook and on-demand download use.
     */
    public function downloadSignedDocument(string $signatureRequestId, string $documentId): string
    {
        $resp = $this->client()
            ->accept('application/pdf')
            ->get("/signature_requests/{$signatureRequestId}/documents/{$documentId}/download")
            ->throw();

        return $resp->body(); // raw PDF bytes
    }

    /** Optional: list documents on the SR (to pick the right id if needed) */
    public function listDocuments(string $signatureRequestId): array
    {
        return $this->client()
            ->get("/signature_requests/{$signatureRequestId}/documents")
            ->throw()
            ->json();
    }
}