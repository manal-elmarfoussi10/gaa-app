<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YousignService
{
    protected string $base;
    protected string $token;

    public function __construct()
    {
        $this->base  = config('services.yousign.base_url');
        $this->token = config('services.yousign.api_key');
    }

    protected function client()
    {
        return Http::withToken($this->token)->baseUrl($this->base);
    }

    public function createProcedure(string $name): array
    {
        return $this->client()->post('/procedures', ['name' => $name])->throw()->json();
    }

    public function uploadFile(string $procedureId, string $path, string $filename): array
    {
        return $this->client()->attach('file', file_get_contents($path), $filename)
            ->post("/procedures/{$procedureId}/files")
            ->throw()
            ->json();
    }

    public function addRecipient(string $procedureId, array $member): array
    {
        return $this->client()->post("/procedures/{$procedureId}/members", $member)->throw()->json();
    }

    public function startProcedure(string $procedureId): array
    {
        return $this->client()->post("/procedures/{$procedureId}/start")->throw()->json();
    }
}