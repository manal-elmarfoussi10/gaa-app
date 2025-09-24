<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class YousignService
{
    protected string $base;
    protected string $token;

    public function __construct()
    {
        $this->base  = (string) config('services.yousign.base_url') ?: 'https://api-sandbox.yousign.com/v3';
        $this->token = (string) config('services.yousign.api_key');

        if (!$this->token) {
            throw new RuntimeException('YOUSIGN_API_KEY is missing.');
        }
    }

    protected function client(): PendingRequest
    {
        return Http::withToken($this->token)
            ->baseUrl($this->base)
            ->acceptJson();
    }

    /** 1) Create signature request */
    public function createSignatureRequest(string $name, string $timezone = 'Europe/Paris', string $deliveryMode = 'email'): array
    {
        return $this->client()
            ->post('/signature_requests', [
                'name'          => $name,
                'delivery_mode' => $deliveryMode,
                'timezone'      => $timezone,
            ])
            ->throw()
            ->json();
    }

    /** 2) Upload a PDF document to the request (multipart) */
    public function uploadDocument(string $signatureRequestId, string $absolutePdfPath): array
    {
        if (!is_file($absolutePdfPath)) {
            throw new RuntimeException("PDF not found at: {$absolutePdfPath}");
        }

        return $this->client()
            ->asMultipart()
            ->attach(
                'file',
                file_get_contents($absolutePdfPath),
                basename($absolutePdfPath)
            )
            ->post("/signature_requests/{$signatureRequestId}/documents", [
                'nature'        => 'signable_document',
                'parse_anchors' => true,
            ])
            ->throw()
            ->json();
    }

    /** 3) Add signer (with one signature field on page 1 at x/y—adjust to your PDF) */
    public function addSigner(
        string $signatureRequestId,
        string $documentId,
        string $email,
        string $firstName,
        string $lastName,
        int $page = 1,
        int $x = 77,
        int $y = 581
    ): array {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/signers", [
                'info' => [
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'locale'     => 'fr',
                ],
                'signature_authentication_mode' => 'no_otp',               // sandbox friendly
                'signature_level'               => 'electronic_signature', // standard level
                'fields' => [[
                    'type'        => 'signature',
                    'document_id' => $documentId,
                    'page'        => $page,
                    'x'           => $x,
                    'y'           => $y,
                    'width'       => 150,   // tweak to fit your contract
                    'height'      => 40,
                ]],
            ])
            ->throw()
            ->json();
    }

    /** 4) Activate signature request (sends emails) */
    public function activate(string $signatureRequestId): array
    {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/activate")
            ->throw()
            ->json();
    }

    /** High-level helper you can call from controller */
    public function sendContract(string $pdfPath, array $signer, string $name = 'Contrat'): array
    {
        // 1
        $sr = $this->createSignatureRequest($name);

        // 2
        $doc = $this->uploadDocument($sr['id'], $pdfPath);

        // 3
        $this->addSigner(
            $sr['id'],
            $doc['id'],
            $signer['email'],
            $signer['first_name'],
            $signer['last_name'],
            page: 1, x: 100, y: 600
        );

        // 4
        $activated = $this->activate($sr['id']);

        return [
            'signature_request' => $sr,
            'document'          => $doc,
            'activated'         => $activated,
        ];
    }
}