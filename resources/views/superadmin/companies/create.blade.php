@extends('layout')
@section('title','Créer une société')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto">
  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">
        Créer une <span class="text-[#FF4B00]">Société</span>
      </h1>
      <p class="text-gray-500 text-sm">
        Ajoutez une nouvelle société et, si besoin, son premier utilisateur.
      </p>
    </div>
  </div>

  <!-- Form -->
  <form action="{{ route('superadmin.companies.store') }}" method="POST"
        class="bg-white rounded-2xl shadow-sm border p-6 space-y-6">
    @csrf

    <!-- Company Fields -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Nom *</label>
        <input name="name"
               class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]"
               required
               value="{{ old('name') }}">
        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
        <input type="email" name="email"
               class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]"
               value="{{ old('email') }}">
        @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Téléphone</label>
        <input name="phone"
               class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]"
               value="{{ old('phone') }}">
        @error('phone')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <!-- Optional User Section -->
    @php
      $wantAdmin = old('create_admin', false);
    @endphp

    <div class="rounded-xl border bg-gray-50">
      <div class="flex items-center justify-between px-5 py-4">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="create_admin" value="1"
                 @checked($wantAdmin)
                 id="toggleCreateAdmin"
                 class="text-[#FF4B00]">
          <span class="font-semibold text-gray-700">Créer aussi un utilisateur</span>
        </label>
        <small class="text-gray-500">Cochez pour créer le premier compte (admin, etc.).</small>
      </div>

      <div id="adminFields" class="px-5 pb-5 @if(!$wantAdmin) hidden @endif">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-1">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Prénom *</label>
            <input name="admin[first_name]"
                   class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]"
                   value="{{ old('admin.first_name') }}">
            @error('admin.first_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Nom *</label>
            <input name="admin[last_name]"
                   class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]"
                   value="{{ old('admin.last_name') }}">
            @error('admin.last_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Email *</label>
            <input type="email" name="admin[email]"
                   class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]"
                   value="{{ old('admin.email') }}">
            @error('admin.email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Rôle *</label>
            <select name="admin[role]"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]">
              @foreach($roles as $value => $label)
                <option value="{{ $value }}"
                        @selected(old('admin.role') == $value)>{{ $label }}</option>
              @endforeach
            </select>
            @error('admin.role')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Mot de passe *</label>
            <input type="password" name="admin[password]"
                   class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]">
            @error('admin.password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Confirmer *</label>
            <input type="password" name="admin[password_confirmation]"
                   class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]">
          </div>

          <label class="inline-flex items-center gap-2 mt-2">
            <input type="checkbox" name="admin[is_active]" value="1" class="text-[#FF4B00]"
                   @checked(old('admin.is_active', true))>
            <span class="text-sm">Activer le compte</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Submit -->
    <div class="flex justify-end">
      <button class="px-6 py-3 bg-[#FF4B00] text-white font-semibold rounded-full shadow hover:bg-[#e04300] transition">
        <i class="fas fa-save mr-2"></i> Enregistrer
      </button>
    </div>
  </form>
</div>

{{-- tiny helper to show/hide nested fields --}}
<script>
  const toggle = document.getElementById('toggleCreateAdmin');
  const block  = document.getElementById('adminFields');
  if (toggle) {
    toggle.addEventListener('change', () => {
      block.classList.toggle('hidden', !toggle.checked);
    });
  }
</script>
@endsection