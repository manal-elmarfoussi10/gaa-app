@extends('layout')
@section('title', 'Modifier '.$user->first_name.' '.$user->last_name)

@section('content')
<div class="px-6 py-6 max-w-5xl mx-auto">

  {{-- Success banner --}}
  @if (session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
      </div>
    </div>
  @endif

  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">
      Modifier <span class="text-[#FF4B00]">{{ $user->first_name }} {{ $user->last_name }}</span>
    </h1>
    <p class="text-gray-500 text-sm">
      Mettez à jour les informations de l’utilisateur de
      <span class="font-medium">{{ $company->name }}</span>.
    </p>
  </div>

  <form method="POST"
        action="{{ route('superadmin.companies.users.update', [$company, $user]) }}"
        class="bg-white rounded-2xl shadow-sm border p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
    @csrf
    @method('PUT')

    {{-- First name --}}
    <div>
      <label class="block text-sm font-medium text-gray-600 mb-1">Prénom *</label>
      <input name="first_name"
             value="{{ old('first_name', $user->first_name) }}"
             class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]" required>
      @error('first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Last name --}}
    <div>
      <label class="block text-sm font-medium text-gray-600 mb-1">Nom *</label>
      <input name="last_name"
             value="{{ old('last_name', $user->last_name) }}"
             class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]" required>
      @error('last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Email --}}
    <div class="md:col-span-1">
      <label class="block text-sm font-medium text-gray-600 mb-1">Email *</label>
      <input type="email" name="email"
             value="{{ old('email', $user->email) }}"
             class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]" required>
      @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Role --}}
    <div class="md:col-span-1">
      <label class="block text-sm font-medium text-gray-600 mb-1">Rôle *</label>
      @php
        $roleOptions = isset($roles) ? $roles : \App\Models\User::roles();
      @endphp
      <select name="role"
              class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]" required>
        @foreach($roleOptions as $value => $label)
          <option value="{{ $value }}" @selected(old('role', $user->role) == $value)>{{ $label }}</option>
        @endforeach
      </select>
      @error('role') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Password (optional) + toggle --}}
    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-600 mb-1">
        Mot de passe <span class="text-gray-400">(laisser vide si inchangé)</span>
      </label>
      <div class="relative">
        <input type="password" name="password" id="password"
               class="w-full border rounded-lg p-3 pr-11 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]">
        <button type="button" class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                aria-label="Afficher le mot de passe"
                data-toggle="password" data-target="#password">
          <i class="fa-regular fa-eye"></i>
        </button>
      </div>
      @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Password confirmation + toggle --}}
    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-600 mb-1">Confirmer le mot de passe</label>
      <div class="relative">
        <input type="password" name="password_confirmation" id="password_confirmation"
               class="w-full border rounded-lg p-3 pr-11 focus:ring-2 focus:ring-[#FF4B00] focus:border-[#FF4B00]">
        <button type="button" class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                aria-label="Afficher la confirmation"
                data-toggle="password" data-target="#password_confirmation">
          <i class="fa-regular fa-eye"></i>
        </button>
      </div>
    </div>

    {{-- Active checkbox --}}
    <div class="md:col-span-2">
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1"
               class="text-[#FF4B00]"
               @checked(old('is_active', (bool) $user->is_active))>
        <span class="text-sm">Activer l’utilisateur</span>
      </label>
      @error('is_active') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Actions --}}
    <div class="md:col-span-2 flex justify-end gap-3 mt-2">
      <a href="{{ route('superadmin.companies.show', $company) }}"
         class="px-5 py-3 rounded-full border text-gray-700 hover:bg-gray-50">Annuler</a>
      <button class="px-6 py-3 bg-[#FF4B00] text-white font-semibold rounded-full shadow hover:bg-[#e04300] transition">
        Enregistrer
      </button>
    </div>
  </form>
</div>

{{-- tiny password toggle helper --}}
<script>
  document.querySelectorAll('[data-toggle="password"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.querySelector(btn.getAttribute('data-target'));
      if (!input) return;
      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';
      // swap eye icon style
      const icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
        // ensure correct regular/solid style
        icon.classList.toggle('fa-regular');
        icon.classList.toggle('fa-solid');
      }
    });
  });
</script>
@endsection