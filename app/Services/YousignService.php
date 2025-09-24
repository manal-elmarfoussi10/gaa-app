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
        // pull from config, then env, then safe default
        $this->base  = (string) (config('services.yousign.base_url') ?: env('YOUSIGN_BASE_URL') ?: 'https://api-sandbox.yousign.com/v3');
        $this->token = (string) (config('services.yousign.api_key')  ?: env('YOUSIGN_API_KEY')  ?: '');

        if (!$this->token) {
            throw new \RuntimeException('YOUSIGN_API_KEY is missing.');
        }

        // normalize
        $this->base = rtrim($this->base, '/');
    }

    protected function client(): PendingRequest
    {
        return Http::asJson()
            ->acceptJson()
            ->withToken($this->token);
            // NOTE: we’ll send absolute URLs below, so baseUrl() is optional now
    }

    // ---- minimal creation, enrich later with files/members ----
    public function createSignatureRequest(string $name): array
    {
        return $this->client()
            ->post($this->base . '/signature_requests', [
                'name' => $name,
            ])
            ->throw()
            ->json();
    }

    public function uploadFile(string $signatureRequestId, string $path, string $filename): array
    {
        return $this->client()
            ->attach('file', file_get_contents($path), $filename)
            ->post($this->base . "/signature_requests/{$signatureRequestId}/files")
            ->throw()
            ->json();
    }

    public function addRecipient(string $signatureRequestId, array $member): array
    {
        return $this->client()
            ->post($this->base . "/signature_requests/{$signatureRequestId}/recipients", $member)
            ->throw()
            ->json();
    }

    public function startSignatureRequest(string $signatureRequestId): array
    {
        return $this->client()
            ->post($this->base . "/signature_requests/{$signatureRequestId}/start")
            ->throw()
            ->json();
    }

    // convenience for your controller
    public function sendContract(\App\Models\Client $client): array
    {
        $sr = $this->createSignatureRequest("Contrat GS Auto - {$client->nom_assure}");

        // add file/recipient here (example only):
        // $this->uploadFile($sr['id'], storage_path('app/contracts/template.pdf'), 'contrat.pdf');
        // $this->addRecipient($sr['id'], [
        //   'firstname' => $client->prenom ?: $client->nom_assure,
        //   'lastname'  => $client->nom_assure,
        //   'email'     => $client->email,
        // ]);

        // start
        return $this->startSignatureRequest($sr['id']);
    }
}