@extends('layout')
@section('title','Créer une société')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto">
  {{-- Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">
        Créer une <span class="text-[#FF4B00]">Société</span>
      </h1>
      <p class="text-gray-500 text-sm">Ajoutez une nouvelle société et, si besoin, son premier utilisateur.</p>
    </div>
  </div>

  {{-- Flash/errors --}}
  @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3">{{ session('success') }}</div>
  @endif
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
        <x-sa.input name="name" label="Nom *" required :value="old('name')" />
        <x-sa.input name="commercial_name" label="Nom commercial" :value="old('commercial_name')" />
        <x-sa.input name="phone" label="Téléphone" :value="old('phone')" />
        <x-sa.input type="email" name="email" label="Email" :value="old('email')" />
      </div>
    </div>

    {{-- Adresse --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
        <i data-lucide="map-pin" class="w-5 h-5 text-[#FF4B00]"></i> Adresse
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-sa.input name="address" label="Adresse" :value="old('address')" class="md:col-span-2" />
        <x-sa.input name="postal_code" label="Code postal" :value="old('postal_code')" />
        <x-sa.input name="city" label="Ville" :value="old('city')" />
      </div>
    </div>

    {{-- Juridique --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
        <i data-lucide="scale" class="w-5 h-5 text-[#FF4B00]"></i> Informations légales
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-sa.input name="legal_form" label="Forme juridique" :value="old('legal_form')" />
        <x-sa.input type="number" step="0.01" name="capital" label="Capital (EUR)" :value="old('capital')" />
        <x-sa.input name="siret" label="SIRET" :value="old('siret')" />
        <x-sa.input name="tva" label="N° TVA" :value="old('tva')" />
        <x-sa.input name="rcs_number" label="RCS n°" :value="old('rcs_number')" />
        <x-sa.input name="rcs_city" label="RCS Ville" :value="old('rcs_city')" />
        <x-sa.input name="ape" label="APE" :value="old('ape')" />
        <x-sa.input name="naf_code" label="Code NAF" :value="old('naf_code')" />
      </div>
    </div>

    {{-- Bancaire / paiement --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
        <i data-lucide="credit-card" class="w-5 h-5 text-[#FF4B00]"></i> Paiement
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-sa.input name="payment_method" label="Méthode de paiement" :value="old('payment_method')" />
        <x-sa.input name="iban" label="IBAN" :value="old('iban')" />
        <x-sa.input name="bic" label="BIC" :value="old('bic')" />
        <x-sa.input name="penalty_rate" label="Taux pénalités (%)" :value="old('penalty_rate')" />
      </div>
    </div>

    {{-- Divers / conformité --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
        <i data-lucide="file-check-2" class="w-5 h-5 text-[#FF4B00]"></i> Divers
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-sa.input name="known_by" label="Connu par" :value="old('known_by')" />
        <x-sa.input name="contact_permission" label="Permission contact" :value="old('contact_permission')" />
        <x-sa.input name="garage_type" label="Type de garage" :value="old('garage_type')" />
        <x-sa.input name="representative" label="Représentant légal" :value="old('representative')" />
        <x-sa.input name="professional_insurance" label="Assurance pro" :value="old('professional_insurance')" />
        <x-sa.input name="tva_regime" label="Régime TVA" :value="old('tva_regime')" />
        <x-sa.input name="eco_contribution" label="Éco-contribution" :value="old('eco_contribution')" />
      </div>
    </div>

    {{-- Fichiers --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
        <i data-lucide="paperclip" class="w-5 h-5 text-[#FF4B00]"></i> Fichiers
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-sa.file name="logo" label="Logo" />
        <x-sa.file name="signature_path" label="Signature (png/jpg)" />
        <x-sa.file name="rib" label="RIB" />
        <x-sa.file name="kbis" label="KBIS" />
        <x-sa.file name="id_photo_recto" label="ID recto" />
        <x-sa.file name="id_photo_verso" label="ID verso" />
        <x-sa.file name="tva_exemption_doc" label="Justif. TVA" />
        <x-sa.file name="invoice_terms_doc" label="CG Facturation" />
      </div>
      <p class="text-xs text-gray-500 mt-2">Formats: png, jpg, jpeg, pdf. Max 4 Mo / fichier.</p>
    </div>

    {{-- Créer un utilisateur optionnel --}}
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
          <x-sa.input name="admin[first_name]" label="Prénom *" :value="old('admin.first_name')" />
          <x-sa.input name="admin[last_name]" label="Nom *" :value="old('admin.last_name')" />
          <x-sa.input type="email" name="admin[email]" label="Email *" :value="old('admin.email')" />
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Rôle *</label>
            <select name="admin[role]"
              class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]">
              @foreach($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('admin.role')==$value)>{{ $label }}</option>
              @endforeach
            </select>
            @error('admin.role')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
          <x-sa.input type="password" name="admin[password]" label="Mot de passe *" />
          <x-sa.input type="password" name="admin[password_confirmation]" label="Confirmer *" />
          <label class="inline-flex items-center gap-2 mt-2">
            <input type="checkbox" name="admin[is_active]" value="1" class="text-[#FF4B00]" @checked(old('admin.is_active', true))>
            <span class="text-sm">Activer le compte</span>
          </label>
        </div>
      </div>
    </div>

    <div class="flex justify-end">
      <button class="px-6 py-3 bg-[#FF4B00] text-white font-semibold rounded-full shadow hover:bg-[#e04300] transition">
        Enregistrer
      </button>
    </div>
  </form>
</div>

{{-- Inputs components (quick inline to avoid includes) --}}
@once
@push('scripts')
<script>
  const toggle = document.getElementById('toggleCreateAdmin');
  const block  = document.getElementById('adminFields');
  if (toggle) toggle.addEventListener('change', () => block.classList.toggle('hidden', !toggle.checked));
</script>
@endpush
@endonce

{{-- Tiny component macros to keep markup DRY --}}
@php
// simple "component-like" helpers
@endphp

@verbatim
{{-- These are pseudo-components. If you use Blade components, move to resources/views/components/sa/*. --}}
@endverbatim

{{-- text input --}}
@php
  if (!function_exists('sa_input')) {
    function sa_input($name,$label,$type='text',$value=null,$attrs='',$required=false){
      $req=$required?'required':'';
      return <<<HTML
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">{$label}</label>
          <input type="{$type}" name="{$name}" value="{$value}" {$req}
            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]" {$attrs}>
        </div>
      HTML;
    }
  }
@endphp
@endsection