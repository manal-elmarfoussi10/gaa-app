@extends('layout')
@section('title','Modifier la société')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-gray-800">
      Modifier <span class="text-[#FF4B00]">{{ $company->name }}</span>
    </h1>
    <p class="text-gray-500 text-sm">Mettez à jour les informations de la société.</p>
  </div>

  {{-- DEBUG: show changed fields after redirect from update --}}
@if(session('debug_changed'))
<div class="rounded-xl bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3">
  <div class="font-semibold mb-1">Champs modifiés :</div>
  <div class="text-sm">{{ implode(', ', session('debug_changed')) }}</div>
</div>
@endif

{{-- DEBUG: show route name to ensure you’re on superadmin update --}}
<div class="rounded-xl bg-gray-50 border px-4 py-2 text-xs text-gray-600">
Route actuelle : {{ request()->route()->getName() ?? 'n/a' }}
</div>

  {{-- Flash messages --}}
  @if(session('success'))
    <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3">
      {{ session('error') }}
    </div>
  @endif

  {{-- Global validation summary --}}
  @if ($errors->any())
    <div class="mb-2 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3">
      <div class="font-semibold mb-1">Veuillez corriger les erreurs :</div>
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- DEBUG drawer (only when ?debug=1 is present) --}}
  @if(request()->boolean('debug'))
    <details class="rounded-xl border p-4 bg-gray-50">
      <summary class="cursor-pointer font-semibold text-gray-700">Debug</summary>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 text-xs">
        <div class="bg-white border rounded p-3 overflow-auto">
          <div class="font-semibold mb-2">Company (current)</div>
          <pre class="whitespace-pre-wrap">@php echo e(json_encode($company->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); @endphp</pre>
        </div>
        <div class="bg-white border rounded p-3 overflow-auto">
          <div class="font-semibold mb-2">Validation errors</div>
          <pre class="whitespace-pre-wrap">@php echo e(json_encode($errors->getMessages(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); @endphp</pre>
        </div>
        <div class="bg-white border rounded p-3 overflow-auto">
          <div class="font-semibold mb-2">Old() payload</div>
          <pre class="whitespace-pre-wrap">@php echo e(json_encode(old(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); @endphp</pre>
        </div>
      </div>
    </details>
  @endif

  <form action="{{ route('superadmin.companies.update', $company) }}" method="POST" enctype="multipart/form-data"
        class="bg-white rounded-2xl shadow-sm border p-6 space-y-8">
    @csrf
    @method('PUT')

    {{-- Identité --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3">Identité</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Nom *</label>
          <input name="name" required value="{{ old('name',$company->name) }}" class="w-full border rounded-lg p-3">
          @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Nom commercial</label>
          <input name="commercial_name" value="{{ old('commercial_name',$company->commercial_name) }}" class="w-full border rounded-lg p-3">
          @error('commercial_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Téléphone</label>
          <input name="phone" value="{{ old('phone',$company->phone) }}" class="w-full border rounded-lg p-3">
          @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-3">
          <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
          <input type="email" name="email" value="{{ old('email',$company->email) }}" class="w-full border rounded-lg p-3">
          @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    {{-- Adresse --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3">Adresse</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-600 mb-1">Adresse</label>
          <input name="address" value="{{ old('address',$company->address) }}" class="w-full border rounded-lg p-3">
          @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Code postal</label>
          <input name="postal_code" value="{{ old('postal_code',$company->postal_code) }}" class="w-full border rounded-lg p-3">
          @error('postal_code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-3">
          <label class="block text-sm font-medium text-gray-600 mb-1">Ville</label>
          <input name="city" value="{{ old('city',$company->city) }}" class="w-full border rounded-lg p-3">
          @error('city') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    {{-- Légal --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3">Informations légales</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Forme juridique</label>
          <input name="legal_form" value="{{ old('legal_form',$company->legal_form) }}" class="w-full border rounded-lg p-3">
          @error('legal_form') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Capital (EUR)</label>
          <input type="number" step="0.01" name="capital" value="{{ old('capital',$company->capital) }}" class="w-full border rounded-lg p-3">
          @error('capital') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">SIRET</label>
          <input name="siret" value="{{ old('siret',$company->siret) }}" class="w-full border rounded-lg p-3">
          @error('siret') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">N° TVA</label>
          <input name="tva" value="{{ old('tva',$company->tva) }}" class="w-full border rounded-lg p-3">
          @error('tva') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">RCS n°</label>
          <input name="rcs_number" value="{{ old('rcs_number',$company->rcs_number) }}" class="w-full border rounded-lg p-3">
          @error('rcs_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">RCS Ville</label>
          <input name="rcs_city" value="{{ old('rcs_city',$company->rcs_city) }}" class="w-full border rounded-lg p-3">
          @error('rcs_city') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">APE</label>
          <input name="ape" value="{{ old('ape',$company->ape) }}" class="w-full border rounded-lg p-3">
          @error('ape') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Code NAF</label>
          <input name="naf_code" value="{{ old('naf_code',$company->naf_code) }}" class="w-full border rounded-lg p-3">
          @error('naf_code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    {{-- Paiement --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3">Paiement</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Méthode de paiement</label>
          <input name="payment_method" value="{{ old('payment_method',$company->payment_method) }}" class="w-full border rounded-lg p-3">
          @error('payment_method') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">IBAN</label>
          <input name="iban" value="{{ old('iban',$company->iban) }}" class="w-full border rounded-lg p-3">
          @error('iban') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">BIC</label>
          <input name="bic" value="{{ old('bic',$company->bic) }}" class="w-full border rounded-lg p-3">
          @error('bic') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Taux pénalités (%)</label>
          <input name="penalty_rate" value="{{ old('penalty_rate',$company->penalty_rate) }}" class="w-full border rounded-lg p-3">
          @error('penalty_rate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    {{-- Divers --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3">Divers</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Connu par</label>
          <input name="known_by" value="{{ old('known_by',$company->known_by) }}" class="w-full border rounded-lg p-3">
          @error('known_by') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Permission contact</label>
          <input name="contact_permission" value="{{ old('contact_permission',$company->contact_permission) }}" class="w-full border rounded-lg p-3">
          @error('contact_permission') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Type de garage</label>
          <input name="garage_type" value="{{ old('garage_type',$company->garage_type) }}" class="w-full border rounded-lg p-3">
          @error('garage_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Représentant légal</label>
          <input name="representative" value="{{ old('representative',$company->representative) }}" class="w-full border rounded-lg p-3">
          @error('representative') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Assurance pro</label>
          <input name="professional_insurance" value="{{ old('professional_insurance',$company->professional_insurance) }}" class="w-full border rounded-lg p-3">
          @error('professional_insurance') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Régime TVA</label>
          <input name="tva_regime" value="{{ old('tva_regime',$company->tva_regime) }}" class="w-full border rounded-lg p-3">
          @error('tva_regime') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Éco-contribution</label>
          <input name="eco_contribution" value="{{ old('eco_contribution',$company->eco_contribution) }}" class="w-full border rounded-lg p-3">
          @error('eco_contribution') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    {{-- Fichiers --}}
    <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-3">Fichiers</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach([
          'logo'=>'Logo',
          'signature_path'=>'Signature (png/jpg)',
          'rib'=>'RIB',
          'kbis'=>'KBIS',
          'id_photo_recto'=>'ID recto',
          'id_photo_verso'=>'ID verso',
          'tva_exemption_doc'=>'Justif. TVA',
          'invoice_terms_doc'=>'CG Facturation'
        ] as $field=>$label)
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">{{ $label }}</label>
            <input type="file" name="{{ $field }}" class="w-full border rounded-lg p-2">
            @error($field) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            @php $path = $company->{$field}; @endphp
            @if($path)
              <div class="mt-2 text-sm">
                <a class="text-[#FF4B00] underline" href="{{ route('attachment', ['path'=>$path]) }}" target="_blank">Voir le fichier actuel</a>
              </div>
            @endif
          </div>
        @endforeach
      </div>
      <p class="text-xs text-gray-500 mt-2">Formats: png, jpg, jpeg, pdf. Max 4 Mo / fichier.</p>
    </div>

    <div class="flex justify-end">
      <button class="px-6 py-3 bg-[#FF4B00] text-white font-semibold rounded-full shadow hover:bg-[#e04300] transition">
        Enregistrer
      </button>
    </div>
  </form>
</div>
@endsection