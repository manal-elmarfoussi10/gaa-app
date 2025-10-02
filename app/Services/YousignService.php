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

    public function getSignatureRequest(string $id): array
{
    return $this->client()->get("/signature_requests/{$id}")->throw()->json();
}

public function downloadSignedPdf(string $signatureRequestId): string
{
    // If Yousign exposes a “files” link on the SR, follow it and GET the PDF bytes.
    // In many setups you first list "exported files" for the SR and then GET the file.
    // Minimal example against a direct export endpoint:
    $resp = $this->client()
        ->get("/signature_requests/{$signatureRequestId}/download") // adjust to your account’s export route
        ->throw();

    return $resp->body(); // raw PDF bytes
}

public function downloadSignedDocument(string $signatureRequestId, string $documentId): string
{
    // v3 style endpoint (adjust if your wrapper uses a different helper)
    // Should return raw PDF bytes.
    $path = "signature_requests/{$signatureRequestId}/documents/{$documentId}/download";
    return $this->getRaw($path); // implement getRaw() to return the binary body
}
protected function getRaw(string $path): string
{
    $resp = $this->http()->get($path); // $this->http() returns a configured pending request
    if ($resp->failed()) {
        throw new \RuntimeException('Yousign download failed: '.$resp->body());
    }
    return $resp->body(); // bytes
}
}