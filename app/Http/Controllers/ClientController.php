<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

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

        if (auth()->check() && auth()->user()->role !== User::ROLE_SUPERADMIN) {
            $query->where('company_id', auth()->user()->company_id);
        }

        $clients = $query->get();

        $columns = [
            'date'      => 'Date',
            'dossier'   => 'Dossier',
            'statut'    => 'Statut GG Auto',
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
        if (auth()->user()->role !== User::ROLE_SUPERADMIN &&
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
            'photo_vitrage'      => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'photo_carte_verte'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'photo_carte_grise'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'type_cadeau'        => 'nullable|string|max:255',
            'numero_sinistre'    => 'nullable|string|max:255',
            'kilometrage'        => 'nullable|string|max:255',
            'connu_par'          => 'nullable|string|max:255',
            'adresse_pose'       => 'nullable|string|max:255',
            'reference_interne'  => 'nullable|string|max:255',
            'reference_client'   => 'nullable|string|max:255',
            'precision'          => 'nullable|string',
        ]);
    
        // Flags
        $validated['ancien_modele_plaque'] = $request->has('ancien_modele_plaque');
        $validated['reparation']           = $request->has('reparation');
    
        // Attach to current company
        $validated['company_id'] = auth()->user()->company_id ?? null;
    
        // Initial GS Auto status (separate from your existing "statut")
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
        try {
            foreach ($client->conversations as $conversation) {
                $conversation->emails()->delete();
                $conversation->delete();
            }

            $client->factures()->delete();
            $client->devis()->delete();
            $client->delete();

            return redirect()->route('clients.index')->with('success', 'Client supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->route('clients.index')->with('error', 'Erreur: '.$e->getMessage());
        }
    }

    public function exportPdf(Client $client)
    {
        $client->load(['factures', 'avoirs', 'devis', 'photos']);
        $pdf = Pdf::loadView('clients.pdf', compact('client'));
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