<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function create()
    {
        return view('clients.create');
    }

    public function index()
    {
        // Scope to the connected company (superadmin will see all if you use a global scope/bypass)
        $query = Client::with(['factures.avoirs', 'devis'])->latest();

        if (auth()->check() && !auth()->user()->isSupport()) {
            $query->where('company_id', auth()->user()->company_id);
        }


        $clients = $query->get();

        $columns = [
            'date'      => 'Date',
            'dossier'   => 'Dossier',
            'statut'    => 'Statut GS auto',

            'assurance' => 'Assurance N° Sinistre',
            'facture'   => 'Factures (HT)',
            'avoir'     => 'Avoirs (HT)',
            'devis'     => 'Devis (HT)',
            'encaisse'  => 'Encaissé',
            'cadeau'    => 'Cadeau',
            'franchise' => 'Franchise',
            'poseur'    => 'Poseur',
            'vitrage'   => 'Vitrage',
            'phone'     => 'Phone',
            'marge'     => 'Marge',
        ];

        return view('clients.index', compact('clients', 'columns'));
    }

    public function show($id)
    {
        $client = Client::with([
            // Commercial docs
            'devis',                                // id, numero, totals, created_at
            'factures' => function ($q) {           // facture + quick sum of its avoirs
                $q->with(['avoirs'])                // full avoirs if you list them
                  ->withSum('avoirs as total_avoirs', 'montant');
            },
    
            // Media
            'photos',
    
            // Conversations + nested users/replies
            'conversations' => function ($q) {
                $q->with([
                    'creator',
                    'emails' => function ($query) {
                        $query->with([
                            'senderUser',
                            'receiverUser',
                            'replies' => fn ($r) => $r->with('senderUser','receiverUser'),
                        ]);
                    },
                ])->orderBy('created_at', 'desc');
            },
        ])->findOrFail($id);
    
        // (optional) guard by company if you want extra safety beyond middleware
        if (!auth()->user()->isSupport() &&
            (int)$client->company_id !== (int)auth()->user()->company_id) {
            abort(403, 'Accès refusé (mauvaise entreprise).');
        }

    
        $users = User::where('company_id', auth()->user()->company_id)->get();
    
        return view('clients.show', compact('client', 'users'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            // add your rules here if you want strict validation
        ]);

        $data = $request->all();
        $this->handleImageUpdates($request, $client, $data);

        $client->update($data);

        return redirect()
            ->route('clients.show', $client->id)
            ->with('success', 'Dossier mis à jour avec succès');
    }

    protected function handleImageUpdates(Request $request, Client $client, array &$data): void
    {
        foreach (['photo_vitrage', 'photo_carte_verte', 'photo_carte_grise'] as $field) {
            $remove = "remove_" . $field;

            if ($request->boolean($remove)) {
                if ($client->$field) {
                    Storage::disk('public')->delete($client->$field);
                }
                $data[$field] = null;
            }

            if ($request->hasFile($field)) {
                if ($client->$field) {
                    Storage::disk('public')->delete($client->$field);
                }
                $data[$field] = $request->file($field)->store('clients', 'public');
            }
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_assure'         => 'required|string|max:255',
            'prenom'             => 'nullable|string|max:255',
            'email'              => 'nullable|email|max:255',
            'telephone'          => 'nullable|string|max:20',
            'adresse'            => 'nullable|string|max:255',
            'code_postal'        => 'nullable|string|max:10',
            'ville'              => 'nullable|string|max:100',
            'plaque'             => 'nullable|string|max:20',
            'nom_assurance'      => 'nullable|string|max:255',
            'autre_assurance'    => 'nullable|string|max:255',
            'numero_police'      => 'nullable|string|max:100',
            'date_sinistre'      => 'nullable|date',
            'date_declaration'   => 'nullable|date',
            'raison'             => 'nullable|string|max:255',
            'type_vitrage'       => 'nullable|string|max:255',
            'professionnel'      => 'nullable|string|max:255',
    
            // 15 MB (max is in KB)
            'photo_vitrage'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:15360',
            'photo_carte_verte'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:15360',
            'photo_carte_grise'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:15360',
    
            'type_cadeau'        => 'nullable|string|max:255',
            'numero_sinistre'    => 'nullable|string|max:255',
            'kilometrage'        => 'nullable|string|max:255',
            'connu_par'          => 'nullable|string|max:255',
            'adresse_pose'       => 'nullable|string|max:255',
            'reference_interne'  => 'nullable|string|max:255',
            'reference_client'   => 'nullable|string|max:255',
            'precision'          => 'nullable|string',
        ], [
            // Optional nicer message
            'photo_carte_grise.max' => 'La carte grise ne doit pas dépasser 15 Mo.',
            'photo_carte_verte.max' => 'La carte verte ne doit pas dépasser 15 Mo.',
            'photo_vitrage.max'     => 'La photo du vitrage ne doit pas dépasser 15 Mo.',
        ]);
    
        // Flags
        $validated['ancien_modele_plaque'] = $request->has('ancien_modele_plaque');
        $validated['reparation']           = $request->has('reparation');
    
        // Attach to current company
        $validated['company_id'] = auth()->user()->company_id ?? null;
    
        // Initial GS Auto status
        $validated['statut'] = 'Dossier créé';
        $validated['statut_gsauto'] = 'draft';

    
        // Uploads -> store on "public" disk; returns paths like "uploads/xxxx.jpg"
        foreach (['photo_vitrage', 'photo_carte_verte', 'photo_carte_grise'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('uploads', 'public');
            }
        }
    
        // Create client (dossier)
        $client = Client::create($validated);
    
        // Redirect to SHOW with a flag that auto-scrolls/opens the signature block
        return redirect()
            ->route('clients.show', $client->id)
            ->with('open_signature', true)
            ->with('success', 'Dossier client créé. Vous pouvez l’envoyer pour signature.');
    }

    public function storePhoto(Request $request, Client $client)
    {
        $path = $request->file('photo')->store('photos', 'public');

        $client->photos()->create([
            'type' => $request->input('type'),
            'path' => $path,
        ]);

        return back()->with('success', 'Photo ajoutée.');
    }

    public function updateStatutInterne(Request $request, Client $client)
    {
        $request->validate(['statut_interne' => 'nullable|string|max:255']);
        $client->update(['statut_interne' => $request->statut_interne]);

        return back()->with('success', 'Statut interne mis à jour.');
    }

    public function rdvs()
    {
        return $this->hasMany(\App\Models\Rdv::class);
    }

    public function destroy(Client $client)
{
    // (optional) safety: only allow deleting inside same company unless superadmin/support
    if (!auth()->user()->isSupport() &&
        (int)$client->company_id !== (int)auth()->user()->company_id) {
        abort(403, 'Accès refusé (mauvaise entreprise).');
    }

    try {
        DB::transaction(function () use ($client) {

            // ---- Remove document fields stored on disk (single-file fields) ----
            foreach (['photo_vitrage', 'photo_carte_verte', 'photo_carte_grise'] as $f) {
                if ($client->$f) {
                    Storage::disk('public')->delete($client->$f);
                }
            }

            // ---- Delete gallery/photos records + files (if you use a photos table) ----
            if (method_exists($client, 'photos')) {
                $client->photos->each(function ($p) {
                    if ($p->path) Storage::disk('public')->delete($p->path);
                    $p->delete();
                });
            }

            // ---- Conversations -> emails -> replies (and their attachments if any) ----
            if (method_exists($client, 'conversations')) {
                $client->load(['conversations.emails.replies']);

                foreach ($client->conversations as $thread) {
                    foreach ($thread->emails as $email) {
                        // email file
                        if ($email->file_path) {
                            Storage::disk('public')->delete($email->file_path);
                        }
                        // replies + files
                        foreach ($email->replies as $reply) {
                            if ($reply->file_path) {
                                Storage::disk('public')->delete($reply->file_path);
                            }
                            $reply->delete();
                        }
                        $email->delete();
                    }
                    $thread->delete();
                }
            }

            // ---- RDV / calendar items (if relation exists) ----
            if (method_exists($client, 'rdvs')) {
                $client->rdvs()->delete();
            }

            // ---- Factures -> paiements + avoirs (children first), then factures ----
            if (method_exists($client, 'factures')) {
                $client->load(['factures.avoirs', 'factures.paiements']);

                foreach ($client->factures as $facture) {
                    // delete paiements if FK restricts
                    if (method_exists($facture, 'paiements')) {
                        $facture->paiements()->delete();
                    }
                    // delete avoirs
                    if (method_exists($facture, 'avoirs')) {
                        $facture->avoirs()->delete();
                    }
                    // pdfs or attachments on factures? delete here if you store paths
                    if (!empty($facture->pdf_path)) {
                        Storage::disk('public')->delete($facture->pdf_path);
                    }
                    $facture->delete();
                }
            }

            // ---- Devis (and any attached files) ----
            if (method_exists($client, 'devis')) {
                $client->loadMissing('devis');
                foreach ($client->devis as $devis) {
                    if (!empty($devis->pdf_path)) {
                        Storage::disk('public')->delete($devis->pdf_path);
                    }
                    $devis->delete();
                }
            }

            // ---- Bons de commande / autres relations (add if you have them) ----
            if (method_exists($client, 'bonsDeCommande')) {
                $client->bonsDeCommande()->delete();
            }

            // Finally: delete the client itself
            $client->delete();
        });

        return redirect()->route('clients.index')->with('success', 'Client supprimé avec succès');
    } catch (\Throwable $e) {
        // Surface SQL FK message if useful
        $msg = $e->getMessage();
        if ($e->getPrevious()) {
            $msg .= ' | ' . $e->getPrevious()->getMessage();
        }
        return redirect()->route('clients.index')->with('error', 'Suppression impossible: '.$msg);
    }
}

    public function exportPdf(Client $client)
    {
        // Load avoirs THROUGH factures to avoid the ambiguous company_id
        $client->load([
            'factures' => function ($q) {
                $q->with(['avoirs']);   // you can add other nested relations here if needed
            },
            'devis',
            'photos',
        ]);
    
        // (optional) if your Blade expects $client->avoirs, build a flat collection:
        $allAvoirs = $client->factures->flatMap->avoirs;
    
        $pdf = Pdf::loadView('clients.pdf', [
            'client'    => $client,
            'allAvoirs' => $allAvoirs, // use this in the view instead of $client->avoirs
        ]);
    
        $filename = 'client_'.$client->id.'_'.now()->format('Ymd_His').'.pdf';
        return $pdf->download($filename);
    }

    public function sendSignature(Request $request, Client $client)
{
    app(\App\Services\YousignService::class)->sendContract($client);
    $client->update(['statut_gsauto' => 'sent']);
    return back()->with('success', 'TODO: envoyer à Yousign');
}
}