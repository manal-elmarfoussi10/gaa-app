<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class YousignService
{
    protected string $base;
    protected string $token;

    public function __construct()
    {
        $this->base = config('services.yousign.base_url', 'https://api-sandbox.yousign.app/v3');
        $this->token = config('services.yousign.api_key');

        if (!$this->token) {
            throw new RuntimeException('YOUSIGN_API_KEY is missing.');
        }
    }

    protected function client()
    {
        return Http::withToken($this->token)
            ->baseUrl($this->base);
    }

    // 1. Create request
    public function createRequest(string $name): array
    {
        return $this->client()
            ->post('/signature_requests', [
                'name' => $name,
                'delivery_mode' => 'email',
                'timezone' => 'Europe/Paris',
            ])
            ->throw()
            ->json();
    }

    // 2. Upload PDF
    public function uploadDocument(string $signatureRequestId, string $absolutePath): array
    {
        return $this->client()
            ->attach('file', file_get_contents($absolutePath), basename($absolutePath))
            ->post("/signature_requests/{$signatureRequestId}/documents", [
                'nature' => 'signable_document',
            ])
            ->throw()
            ->json();
    }

    // 3. Add signer
    public function addSigner(string $signatureRequestId, string $documentId, array $client): array
    {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/signers", [
                "info" => [
                    "first_name" => $client['prenom'] ?? 'Prénom',
                    "last_name"  => $client['nom'] ?? 'Nom',
                    "email"      => $client['email'],
                    "phone_number" => $client['phone'] ?? "+33600000000",
                    "locale"     => "fr",
                ],
                "signature_authentication_mode" => "no_otp",
                "signature_level" => "electronic_signature",
                "fields" => [[
                    "document_id" => $documentId,
                    "type" => "signature",
                    "page" => 1,
                    "x" => 100,
                    "y" => 100,
                    "height" => 40,
                    "width" => 85,
                ]]
            ])
            ->throw()
            ->json();
    }

    // 4. Activate
    public function activate(string $signatureRequestId): array
    {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/activate")
            ->throw()
            ->json();
    }
}