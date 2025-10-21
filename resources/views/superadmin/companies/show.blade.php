@extends('layout')
@section('title', 'Détails société')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">

  {{-- Header Card --}}
  <div class="bg-white rounded-2xl shadow-sm border p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center">
          <i data-lucide="building-2" class="w-6 h-6 text-[#FF4B00]"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-800">
            Société : <span class="text-[#FF4B00]">{{ $company->name }}</span>
          </h1>
          <div class="text-sm text-gray-500 flex flex-wrap gap-3 mt-1">
            @if($company->created_at)
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100">
                <i data-lucide="calendar" class="w-4 h-4"></i> Créée le {{ $company->created_at->format('d/m/Y') }}
              </span>
            @endif
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100">
              <i data-lucide="users" class="w-4 h-4"></i> {{ $users->count() }} utilisateur(s)
            </span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('superadmin.companies.edit', $company) }}" class="btn-secondary">
          <i data-lucide="pencil" class="w-4 h-4"></i> Modifier
        </a>
        <form action="{{ route('superadmin.companies.destroy', $company) }}" method="POST"
              onsubmit="return confirm('Supprimer cette société ? Tous ses utilisateurs seront supprimés.');">
          @csrf @method('DELETE')
          <button type="submit" class="btn-danger">
            <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Infos principales --}}
  <div class="bg-white rounded-2xl shadow-sm border p-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Informations</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @foreach([
        'Nom' => $company->name,
        'Nom commercial' => $company->commercial_name,
        'Email' => $company->email,
        'Téléphone' => $company->phone,
        'Adresse' => $company->address,
        'Code postal' => $company->postal_code,
        'Ville' => $company->city,
        'Forme juridique' => $company->legal_form,
        'Capital' => $company->capital ? number_format((float)$company->capital,0,',',' ').' €' : null,
        'SIRET' => $company->siret,
        'TVA' => $company->tva,
        'RCS' => trim(($company->rcs_number.' '.$company->rcs_city)),
        'APE' => $company->ape,
        'Code NAF' => $company->naf_code,
        'IBAN' => $company->iban,
        'BIC' => $company->bic,
        'Méthode paiement' => $company->payment_method,
        'Taux pénalités' => $company->penalty_rate,
        'Représentant' => $company->representative,
        'Assurance pro' => $company->professional_insurance,
        'Régime TVA' => $company->tva_regime,
        'Éco-contribution' => $company->eco_contribution,
        'Connu par' => $company->known_by,
        'Permission contact' => $company->contact_permission,
        'Type de garage' => $company->garage_type,
      ] as $label => $value)
        <div class="rounded-xl border p-4">
          <div class="text-xs text-gray-500 mb-1">{{ $label }}</div>
          <div class="font-medium text-gray-800">{{ $value ?: '—' }}</div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Fichiers --}}
  <div class="bg-white rounded-2xl shadow-sm border p-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Documents</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @foreach([
        'logo' => 'Logo',
        'signature_path' => 'Signature',
        'rib' => 'RIB',
        'kbis' => 'KBIS',
        'id_photo_recto' => 'ID recto',
        'id_photo_verso' => 'ID verso',
        'tva_exemption_doc' => 'Justif. TVA',
        'invoice_terms_doc' => 'CG Facturation',
      ] as $field => $label)
        <div class="rounded-xl border p-4">
          <div class="text-xs text-gray-500 mb-1">{{ $label }}</div>
          @php $path = $company->{$field}; @endphp
          @if($path)
            <a href="{{ route('attachment', ['path' => $path]) }}" class="inline-flex items-center gap-2 text-[#FF4B00] underline" target="_blank">
              <i data-lucide="paperclip" class="w-4 h-4"></i> Ouvrir
            </a>
          @else
            <div class="text-gray-500">—</div>
          @endif
        </div>
      @endforeach
    </div>
  </div>

  {{-- Utilisateurs --}}
  <div class="bg-white rounded-2xl shadow-sm border p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-bold text-gray-800">Utilisateurs de cette société</h2>
      <a href="{{ route('superadmin.companies.users.create', $company) }}" class="btn-primary">
        <i data-lucide="user-plus" class="w-4 h-4"></i> Nouvel utilisateur
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="p-3 text-left font-semibold uppercase text-xs tracking-wider">Nom</th>
            <th class="p-3 text-left font-semibold uppercase text-xs tracking-wider">Email</th>
            <th class="p-3 text-left font-semibold uppercase text-xs tracking-wider">Rôle</th>
            <th class="p-3 text-left font-semibold uppercase text-xs tracking-wider">Actif</th>
            <th class="p-3 text-right font-semibold uppercase text-xs tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($users as $u)
            <tr class="hover:bg-gray-50">
              <td class="p-3">
                <div class="flex items-center gap-3">
                  <div class="h-9 w-9 rounded-full bg-orange-50 border border-orange-100 flex items-center justify-center text-[#FF4B00] font-semibold">
                    {{ strtoupper(Str::of($u->name ?: ($u->first_name.' '.$u->last_name))->substr(0,1)) }}
                  </div>
                  <div class="font-medium text-gray-800">{{ $u->name }}</div>
                </div>
              </td>
              <td class="p-3 text-gray-700">{{ $u->email }}</td>
              <td class="p-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-800">{{ $u->role_label }}</span>
              </td>
              <td class="p-3">
                @if($u->is_active)
                  <span class="inline-flex items-center gap-1 text-green-600"><i data-lucide="check-circle" class="w-4 h-4"></i> Oui</span>
                @else
                  <span class="inline-flex items-center gap-1 text-red-600"><i data-lucide="x-circle" class="w-4 h-4"></i> Non</span>
                @endif
              </td>
              <td class="p-3 text-right">
                <div class="inline-flex gap-1">
                  @if(Route::has('superadmin.companies.users.edit'))
                    <a href="{{ route('superadmin.companies.users.edit', [$company, $u]) }}" class="btn-icon text-blue-600 hover:bg-blue-50" title="Modifier">
                      <i data-lucide="pencil" class="w-4 h-4"></i>
                    </a>
                  @endif
                  @if(Route::has('superadmin.companies.users.destroy'))
                    <form action="{{ route('superadmin.companies.users.destroy', [$company, $u]) }}" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?');" class="inline">
                      @csrf @method('DELETE')
                      <button class="btn-icon text-red-600 hover:bg-red-50" title="Supprimer">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="p-8 text-center text-gray-500">Aucun utilisateur trouvé.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
.btn-primary{background:#FF6B00;color:#fff;font-weight:600;padding:10px 16px;border-radius:12px;display:inline-flex;gap:8px;align-items:center;box-shadow:0 4px 12px rgba(255,107,0,.25);transition:all .2s ease;border:none}
.btn-primary:hover{background:#D45A00;transform:translateY(-1px)}
.btn-secondary{background:#fff;color:#1f2937;border:1px solid #e5e7eb;padding:10px 16px;border-radius:12px;display:inline-flex;gap:8px;align-items:center;transition:all .2s ease}
.btn-secondary:hover{background:#f9fafb}
.btn-danger{background:#ef4444;color:#fff;font-weight:600;padding:10px 16px;border-radius:12px;display:inline-flex;gap:8px;align-items:center;transition:all .2s ease;border:none}
.btn-danger:hover{background:#dc2626}
.btn-icon{height:36px;width:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;border:1px solid #e5e7eb;background:#fff;transition:all .2s ease}
.btn-icon:hover{transform:translateY(-1px)}
</style>
@endsection