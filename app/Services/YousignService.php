<?php
// app/Services/YousignService.php
namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use RuntimeException;

class YousignService
{
    protected string $base;
    protected string $token;

    public function __construct()
    {
        $this->base  = (string) (config('services.yousign.base_url') ?: 'https://api-sandbox.yousign.com/v3');
        $this->token = (string) config('services.yousign.api_key');

        if (!$this->token) {
            throw new RuntimeException('YOUSIGN_API_KEY is missing.');
        }
    }

    protected function client()
    {
        return Http::withToken($this->token)->baseUrl($this->base);
    }

    // --- low-level helpers you already had ---
    public function createProcedure(string $name): array
    {
        return $this->client()->post('/procedures', ['name' => $name])->throw()->json();
    }

    public function uploadFile(string $procedureId, string $path, string $filename): array
    {
        return $this->client()
            ->attach('file', file_get_contents($path), $filename)
            ->post("/procedures/{$procedureId}/files")
            ->throw()
            ->json();
    }

    public function addRecipient(string $procedureId, array $member): array
    {
        return $this->client()
            ->post("/procedures/{$procedureId}/members", $member)
            ->throw()
            ->json();
    }

    public function startProcedure(string $procedureId): array
    {
        return $this->client()->post("/procedures/{$procedureId}/start")->throw()->json();
    }

    // --- NEW high-level method used by your controller ---
    public function sendContract(Client $client): array
    {
        // 1) Build a simple PDF contract (replace the view with your own)
        $pdfPath = storage_path('app/tmp');
        if (!is_dir($pdfPath)) mkdir($pdfPath, 0775, true);

        $fileName = 'gsauto-contract-' . $client->id . '-' . Str::uuid() . '.pdf';
        $fullPath = $pdfPath . '/' . $fileName;

        // Create a minimal PDF — swap to your real Blade view later
        $pdf = Pdf::loadView('contracts.gsauto-min', [
            'client'  => $client,
            'company' => auth()->user()->company ?? null,
            'today'   => now(),
        ]);
        $pdf->save($fullPath);

        // 2) Create the procedure
        $proc = $this->createProcedure('GS Auto - Contrat #' . $client->id);

        // 3) Upload the PDF
        $uploaded = $this->uploadFile($proc['id'], $fullPath, $fileName);

        // 4) Add the recipient (signer) – adjust fields to match your data
        $firstname = $client->prenom ?: $client->nom_assure;
        $lastname  = $client->nom_assure ?: ($client->prenom ?: 'Client');

        // NOTE: The exact payload for signature zones depends on your Yousign plan.
        // This uses a simple "file_objects" example (1st page area). Adjust as needed.
        $member = $this->addRecipient($proc['id'], [
            'firstname'    => $firstname,
            'lastname'     => $lastname,
            'email'        => $client->email,
            'phone'        => $client->telephone,
            'file_objects' => [[
                'file'     => $uploaded['id'],
                'page'     => 1,
                'position' => '230,700,430,740', // left,top,right,bottom (adjust)
                'type'     => 'signature',
            ]],
        ]);

        // 5) Start the procedure (sends email to the signer)
        $started = $this->startProcedure($proc['id']);

        return [
            'procedure' => $proc,
            'file'      => $uploaded,
            'member'    => $member,
            'started'   => $started,
            'pdf_path'  => $fullPath,
        ];
    }
}