<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Avoir;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientsController extends Controller
{
    /**
     * Affiche le dossier client (dashboard support).
     * Bypass des global scopes; soft-deletes inclus si le modèle les utilise.
     */
    public function show($id)
    {
        $this->authorizeSupport();

        // Désactiver debugbar si présent (évite le bruit dans PDFs/iframes)
        if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class)) {
            \Barryvdh\Debugbar\Facades\Debugbar::disable();
        }

        // Liste défensive des colonnes (inclure les colonnes "fichiers" si utilisées)
        $candidateCols = [
            'id','company_id','prenom','nom_assure','plaque',
            'email','telephone','adresse','kilometrage','type_vitrage',
            'ancien_modele_plaque','nom_assurance','numero_police','numero_sinistre',
            'autre_assurance','date_sinistre','date_declaration','raison','reparation',
            'connu_par','adresse_pose','precision',
            'statut_signature','statut_verif_bdg','statut_envoi','statut_relance','statut_termine',
            'statut_interne',
            'encaisse','type_cadeau','reference_interne','reference_client',
            'photo_vitrage','photo_carte_verte','photo_carte_grise',
            'created_at',
        ];
        $existingCols = Schema::getColumnListing('clients');
        $selectCols   = array_values(array_intersect($candidateCols, $existingCols));

        // Query sans global scopes; inclure soft-deleted si applicable
        $query = Client::query()->withoutGlobalScopes();
        if (in_array(SoftDeletes::class, class_uses_recursive(Client::class), true)) {
            $query->withTrashed();
        }

        $client = $query
            ->when($selectCols, fn ($q) => $q->select($selectCols))
            ->with([
                // ⚠️ Toujours inclure les FKs dans select() (client_id / facture_id) pour hydrater la relation
                'factures' => fn($q) => $q
                    ->select('id','client_id','numero','total_ht','total_ttc','created_at')
                    ->latest()->limit(100),
                'factures.avoirs' => fn($q) => $q
                    ->select('id','facture_id','montant','created_at')->latest(),
                'devis' => fn($q) => $q
                    ->select('id','client_id','numero','total_ht','total_ttc','created_at')
                    ->latest()->limit(100),

                'photos' => fn($q) => $q->latest()->limit(20),

                'conversations' => function ($q) {
                    $q->with([
                        'creator:id,name',
                        'emails' => function ($q) {
                            $q->select(
                                'id','thread_id','sender_id','receiver_id','content',
                                'file_path','file_name','created_at'
                            )
                            ->latest()->limit(10)
                            ->with([
                                'senderUser:id,name',
                                'receiverUser:id,name',
                                'replies' => function ($qr) {
                                    $qr->select(
                                        'id','email_id','sender_id','receiver_id','content',
                                        'file_path','file_name','created_at'
                                    )
                                    ->latest()->limit(10)
                                    ->with(['senderUser:id,name','receiverUser:id,name']);
                                }
                            ]);
                        }
                    ])->latest();
                }
            ])
            ->findOrFail((int) $id);

        $statutLabel = $this->deriveStatutLabel($client);

        // Liste utilisateurs (même company que le client si possible)
        $companyIdForUsers = $client->company_id ?? auth()->user()->company_id;
        $users = User::query()
            ->withoutGlobalScopes()
            ->when($companyIdForUsers, fn($q) => $q->where('company_id', $companyIdForUsers))
            ->orderBy('name')
            ->get(['id','name']);

        return view('superadmin.clients.show', compact('client','users','statutLabel'));
    }

    protected function deriveStatutLabel(Client $client): string
    {
        if ((int)($client->statut_termine   ?? 0) === 1) return 'Terminé';
        if ((int)($client->statut_relance   ?? 0) === 1) return 'Relancé';
        if ((int)($client->statut_envoi     ?? 0) === 1) return 'Envoyé';
        if ((int)($client->statut_verif_bdg ?? 0) === 1) return 'Vérification BDG';
        if ((int)($client->statut_signature ?? 0) === 1) return 'Signé';
        return 'En attente';
    }

    /**
     * Export dossier complet en PDF (scope-free).
     */
    public function exportPdf($id)
    {
        $this->authorizeSupport();

        $q = Client::query()->withoutGlobalScopes();
        if (in_array(SoftDeletes::class, class_uses_recursive(Client::class), true)) {
            $q->withTrashed();
        }

        $client = $q->with(['factures.avoirs', 'devis', 'photos'])->findOrFail((int) $id);

        $pdf = Pdf::loadView('clients.pdf', compact('client'));
        $filename = 'client_' . $client->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function previewDevis(Devis $devis)
    {
        $this->authorizeSupport();
    
        // Devis doesn't need its own company() if we can reach it via client
        $devis->load(['items', 'client.company']);
    
        $company = $devis->client?->company;
    
        // You have: resources/views/devis/single-pdf.blade.php
        return Pdf::loadView('devis.single-pdf', compact('devis', 'company'))
                  ->stream("devis_{$devis->numero}.pdf");
    }
    
    public function previewFacture(Facture $facture)
    {
        $this->authorizeSupport();
    
        // Remove 'company' from the eager load (no relation on the model)
        $facture->load(['items', 'client.company']);
    
        $company = $facture->client?->company;
    
        // You have: resources/views/factures/pdf.blade.php
        return Pdf::loadView('factures.pdf', compact('facture', 'company'))
                  ->stream("facture_{$facture->numero}.pdf");
    }
    
    public function previewAvoir(Avoir $avoir)
    {
        $this->authorizeSupport();
    
        // Reach the company via facture -> client -> company
        $avoir->load(['facture.client.company']);
    
        $company = $avoir->facture?->client?->company;
    
        // Use the single template you have: resources/views/avoirs/single_pdf.blade.php
        return Pdf::loadView('avoirs.single_pdf', compact('avoir', 'company'))
                  ->stream("avoir_{$avoir->id}.pdf");
    }

    private function authorizeSupport(): void
    {
        $u = auth()->user();
        abort_unless(
            $u && in_array($u->role, [User::ROLE_SUPERADMIN, User::ROLE_CLIENT_SERVICE], true),
            403
        );

    
}