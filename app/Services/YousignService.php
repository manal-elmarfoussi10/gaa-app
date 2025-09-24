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
        $this->base  = (string) (config('services.yousign.base_url') ?: env('YOUSIGN_BASE_URL', 'https://api-sandbox.yousign.com'));
        $this->token = (string) (config('services.yousign.api_key')  ?: env('YOUSIGN_API_KEY'));
    
        if (!$this->token) {
            throw new \RuntimeException('YOUSIGN_API_KEY is missing.');
        }
    }

    protected function client(): PendingRequest
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->base);
    }

    /* ---- New API helpers ---- */

    // 1) Create a signature request (DRAFT)
    public function createSignatureRequest(string $name): array
    {
        // minimal payload – you can enrich later (reminders, custom experience, etc.)
        return $this->client()
            ->post('/signature_requests', [
                'name' => $name,
            ])
            ->throw()
            ->json();
    }

    // 2) Upload a file -> returns { id: "file_xxx", ... }
    public function uploadFile(string $path, string $filename): array
    {
        return $this->client()
            ->attach('file', file_get_contents($path), $filename)
            ->post('/files')
            ->throw()
            ->json();
    }

    // 3) Attach the uploaded file to the signature request as a "document"
    // with at least one signature field (position here is just an example).
    public function attachDocument(string $signatureRequestId, string $fileId): array
    {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/documents", [
                'file_id' => $fileId,
                'nature'  => 'signable_document',
                'fields'  => [[
                    'type'        => 'signature',
                    'page'        => 1,
                    'x'           => 200,   // adjust
                    'y'           => 600,   // adjust
                ]],
            ])
            ->throw()
            ->json();
    }

    // 4) Add a signer to the signature request
    public function addSigner(string $signatureRequestId, array $signer): array
    {
        // $signer must include at least first_name, last_name, email
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/signers", $signer)
            ->throw()
            ->json();
    }

    // 5) Activate (sends emails/SMS depending on config)
    public function activate(string $signatureRequestId): array
    {
        return $this->client()
            ->post("/signature_requests/{$signatureRequestId}/activate")
            ->throw()
            ->json();
    }

    /* ---- One-shot convenience method you can call from your controller ---- */
    public function sendContract(\App\Models\Client $client): array
    {
        // 1) create SR
        $sr = $this->createSignatureRequest("GS Auto – {$client->nom_assure} {$client->prenom}");

        // 2) upload a PDF to sign (use your real contract PDF path)
        // fallback to any existing PDF you already generate/export
        $pdfPath = storage_path('app/contracts/gsauto-contract.pdf'); // make sure this exists
        if (!is_file($pdfPath)) {
            throw new RuntimeException('Contract PDF not found at ' . $pdfPath);
        }
        $file = $this->uploadFile($pdfPath, 'contrat-gsauto.pdf');

        // 3) attach the document with a signature field
        $this->attachDocument($sr['id'], $file['id']);

        // 4) add the signer
        $this->addSigner($sr['id'], [
            'first_name' => $client->prenom ?: $client->nom_assure,
            'last_name'  => $client->nom_assure ?: ($client->prenom ?: 'Client'),
            'email'      => $client->email ?: 'test@example.com', // MUST be a valid email in sandbox
        ]);

        // 5) activate (actually sends the request)
        return $this->activate($sr['id']);
    }
}