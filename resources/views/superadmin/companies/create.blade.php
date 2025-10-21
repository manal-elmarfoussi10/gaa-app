@extends('layout')
@section('title','Créer une société')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">
        Créer une <span class="text-[#FF4B00]">Société</span>
      </h1>
      <p class="text-gray-500 text-sm">Ajoutez une nouvelle société et, si besoin, son premier utilisateur.</p>
    </div>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3">
      <div class="font-semibold mb-1">Veuillez corriger les erreurs :</div>
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('superadmin.companies.store') }}" method="POST" enctype="multipart/form-data"
        class="bg-white rounded-2xl shadow-sm border p-6 space-y-8">
    @csrf

    {{-- Identité --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
        <i data-lucide="building-2" class="w-5 h-5 text-[#FF4B00]"></i> Identité
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Nom *</label>
          <input name="name" required value="{{ old('name') }}"
                 class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Nom commercial</label>
          <input name="commercial_name" value="{{ old('commercial_name') }}" class="w-full border rounded-lg p-3">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Téléphone</label>
          <input name="phone" value="{{ old('phone') }}" class="w-full border rounded-lg p-3">
        </div>
        <div class="md:col-span-3">
          <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg p-3">
        </div>
      </div>
    </div>

    {{-- Adresse --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3"><i data-lucide="map-pin" class="w-5 h-5 text-[#FF4B00]"></i> Adresse</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-600 mb-1">Adresse</label>
          <input name="address" value="{{ old('address') }}" class="w-full border rounded-lg p-3">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Code postal</label>
          <input name="postal_code" value="{{ old('postal_code') }}" class="w-full border rounded-lg p-3">
        </div>
        <div class="md:col-span-3">
          <label class="block text-sm font-medium text-gray-600 mb-1">Ville</label>
          <input name="city" value="{{ old('city') }}" class="w-full border rounded-lg p-3">
        </div>
      </div>
    </div>

    {{-- Légal --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3"><i data-lucide="scale" class="w-5 h-5 text-[#FF4B00]"></i> Informations légales</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Forme juridique</label>
          <input name="legal_form" value="{{ old('legal_form') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Capital (EUR)</label>
          <input type="number" step="0.01" name="capital" value="{{ old('capital') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">SIRET</label>
          <input name="siret" value="{{ old('siret') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">N° TVA</label>
          <input name="tva" value="{{ old('tva') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">RCS n°</label>
          <input name="rcs_number" value="{{ old('rcs_number') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">RCS Ville</label>
          <input name="rcs_city" value="{{ old('rcs_city') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">APE</label>
          <input name="ape" value="{{ old('ape') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Code NAF</label>
          <input name="naf_code" value="{{ old('naf_code') }}" class="w-full border rounded-lg p-3">
        </div>
      </div>
    </div>

    {{-- Paiement --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3"><i data-lucide="credit-card" class="w-5 h-5 text-[#FF4B00]"></i> Paiement</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Méthode de paiement</label>
          <input name="payment_method" value="{{ old('payment_method') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">IBAN</label>
          <input name="iban" value="{{ old('iban') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">BIC</label>
          <input name="bic" value="{{ old('bic') }}" class="w-full border rounded-lg p-3">
        </div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Taux pénalités (%)</label>
          <input name="penalty_rate" value="{{ old('penalty_rate') }}" class="w-full border rounded-lg p-3">
        </div>
      </div>
    </div>

    {{-- Divers --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3"><i data-lucide="file-check-2" class="w-5 h-5 text-[#FF4B00]"></i> Divers</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Connu par</label>
          <input name="known_by" value="{{ old('known_by') }}" class="w-full border rounded-lg p-3"></div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Permission contact</label>
          <input name="contact_permission" value="{{ old('contact_permission') }}" class="w-full border rounded-lg p-3"></div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Type de garage</label>
          <input name="garage_type" value="{{ old('garage_type') }}" class="w-full border rounded-lg p-3"></div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Représentant légal</label>
          <input name="representative" value="{{ old('representative') }}" class="w-full border rounded-lg p-3"></div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Assurance pro</label>
          <input name="professional_insurance" value="{{ old('professional_insurance') }}" class="w-full border rounded-lg p-3"></div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Régime TVA</label>
          <input name="tva_regime" value="{{ old('tva_regime') }}" class="w-full border rounded-lg p-3"></div>
        <div><label class="block text-sm font-medium text-gray-600 mb-1">Éco-contribution</label>
          <input name="eco_contribution" value="{{ old('eco_contribution') }}" class="w-full border rounded-lg p-3"></div>
      </div>
    </div>

    {{-- Fichiers --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3"><i data-lucide="paperclip" class="w-5 h-5 text-[#FF4B00]"></i> Fichiers</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach(['logo'=>'Logo','signature_path'=>'Signature (png/jpg)','rib'=>'RIB','kbis'=>'KBIS','id_photo_recto'=>'ID recto','id_photo_verso'=>'ID verso','tva_exemption_doc'=>'Justif. TVA','invoice_terms_doc'=>'CG Facturation'] as $field=>$label)
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">{{ $label }}</label>
            <input type="file" name="{{ $field }}" class="w-full border rounded-lg p-2">
          </div>
        @endforeach
      </div>
      <p class="text-xs text-gray-500 mt-2">Formats: png, jpg, jpeg, pdf. Max 4 Mo / fichier.</p>
    </div>

    {{-- Utilisateur optionnel --}}
    @php $wantAdmin = old('create_admin', false); @endphp
    <div class="rounded-xl border bg-gray-50">
      <div class="flex items-center justify-between px-5 py-4">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="create_admin" value="1" @checked($wantAdmin) id="toggleCreateAdmin" class="text-[#FF4B00]">
          <span class="font-semibold text-gray-700">Créer aussi un utilisateur</span>
        </label>
        <small class="text-gray-500">Cochez pour créer le premier compte (admin, etc.).</small>
      </div>

      <div id="adminFields" class="px-5 pb-5 @if(!$wantAdmin) hidden @endif">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-1">
          <div><label class="block text-sm font-medium text-gray-600 mb-1">Prénom *</label>
            <input name="admin[first_name]" value="{{ old('admin.first_name') }}" class="w-full border rounded-lg p-3"></div>
          <div><label class="block text-sm font-medium text-gray-600 mb-1">Nom *</label>
            <input name="admin[last_name]" value="{{ old('admin.last_name') }}" class="w-full border rounded-lg p-3"></div>
          <div><label class="block text-sm font-medium text-gray-600 mb-1">Email *</label>
            <input type="email" name="admin[email]" value="{{ old('admin.email') }}" class="w-full border rounded-lg p-3"></div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Rôle *</label>
            <select name="admin[role]" class="w-full border rounded-lg p-3">
              @foreach($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('admin.role')==$value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div><label class="block text-sm font-medium text-gray-600 mb-1">Mot de passe *</label>
            <input type="password" name="admin[password]" class="w-full border rounded-lg p-3"></div>
          <div><label class="block text-sm font-medium text-gray-600 mb-1">Confirmer *</label>
            <input type="password" name="admin[password_confirmation]" class="w-full border rounded-lg p-3"></div>
          <label class="inline-flex items-center gap-2 mt-2">
            <input type="checkbox" name="admin[is_active]" value="1" class="text-[#FF4B00]" @checked(old('admin.is_active', true))>
            <span class="text-sm">Activer le compte</span>
          </label>
        </div>
      </div>
    </div>

    <div class="flex justify-end">
      <button class="px-6 py-3 bg-[#FF4B00] text-white font-semibold rounded-full shadow hover:bg-[#e04300] transition">Enregistrer</button>
    </div>
  </form>
</div>

<script>
  const t = document.getElementById('toggleCreateAdmin'), b = document.getElementById('adminFields');
  if (t) t.addEventListener('change', ()=> b.classList.toggle('hidden', !t.checked));
</script>
@endsection