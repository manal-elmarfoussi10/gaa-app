@extends('layouts.guest')

@section('title', 'Inscription')
@section('shellWidth','max-w-5xl') {{-- keep the page compact, not too narrow, not too wide --}}

@section('content')
{{-- Page background (subtle, no big blobs, no huge whitespace) --}}
<div class="relative isolate bg-gradient-to-b from-orange-50 via-white to-white">
  {{-- top ribbon --}}
  <div class="mx-auto w-full @yield('shellWidth') px-4 pt-6">
    <div class="flex items-center justify-between rounded-2xl bg-white/90 px-5 py-3 shadow ring-1 ring-black/[0.04]">
      <div class="flex items-center gap-3">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-10 w-auto" />
        <div class="leading-tight">
          <div class="font-extrabold text-gray-900">GS Auto</div>
          <div class="text-xs text-gray-500">Gestion de bris de glace</div>
        </div>
      </div>
      <a href="{{ route('login') }}"
         class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-50">
        Déjà inscrit ? <span class="ml-1 text-[#FF4B00]">Se connecter</span>
      </a>
    </div>
  </div>

  {{-- Card --}}
  <div class="mx-auto w-full @yield('shellWidth') px-4 pb-12 pt-6">
    <div class="rounded-3xl bg-white/95 p-6 shadow-xl ring-1 ring-black/[0.04] md:p-10">

      <div class="mb-6 flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 md:text-3xl">Création de votre compte</h1>
          <p class="mt-1 text-sm text-gray-600 md:text-base">Rejoignez GS Auto et créez votre compte professionnel.</p>
        </div>
        <div class="hidden sm:flex gap-2">
          <span class="rounded-full border border-dashed border-gray-300 px-3 py-1 text-xs text-gray-600">Sécurisé</span>
          <span class="rounded-full border border-dashed border-gray-300 px-3 py-1 text-xs text-gray-600">E-signature</span>
          <span class="rounded-full border border-dashed border-gray-300 px-3 py-1 text-xs text-gray-600">Support 24/7</span>
        </div>
      </div>

      {{-- Flash / errors --}}
      @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
          {{ session('success') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
          <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('register') }}" class="space-y-6" novalidate>
        @csrf

        {{-- Identity --}}
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label for="first_name" class="mb-1 block text-sm font-medium text-gray-700">Prénom *</label>
            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="last_name" class="mb-1 block text-sm font-medium text-gray-700">Nom *</label>
            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <div>
          <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Adresse Email *</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Company --}}
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label for="company_name" class="mb-1 block text-sm font-medium text-gray-700">Nom de la société *</label>
            <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('company_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="commercial_name" class="mb-1 block text-sm font-medium text-gray-700">Nom commercial</label>
            <input id="commercial_name" name="commercial_name" type="text" value="{{ old('commercial_name') }}"
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('commercial_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <div>
            <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">Téléphone *</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="siret" class="mb-1 block text-sm font-medium text-gray-700">Numéro de siret *</label>
            <input id="siret" name="siret" type="text" value="{{ old('siret') }}" required
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('siret') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="tva" class="mb-1 block text-sm font-medium text-gray-700">Numéro de TVA *</label>
            <input id="tva" name="tva" type="text" value="{{ old('tva') }}" required
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('tva') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Garage type (clean tiles) --}}
        <div>
          <span class="mb-2 block text-sm font-semibold text-gray-800">Quel type de garage avez-vous ?</span>
          <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <label class="flex w-full cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-[#FF7A1C]">
              <input type="radio" name="garage_type" value="fixe" {{ old('garage_type')=='fixe'?'checked':'' }} class="mt-1" />
              <div class="min-w-0">
                <div class="font-semibold text-gray-900">Fixe</div>
                <div class="text-xs text-gray-600">Changement pare brise sur place</div>
              </div>
            </label>
            <label class="flex w-full cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-[#FF7A1C]">
              <input type="radio" name="garage_type" value="mobile" {{ old('garage_type')=='mobile'?'checked':'' }} class="mt-1" />
              <div class="min-w-0">
                <div class="font-semibold text-gray-900">Mobile</div>
                <div class="text-xs text-gray-600">Changement chez le client</div>
              </div>
            </label>
            <label class="flex w-full cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-[#FF7A1C]">
              <input type="radio" name="garage_type" value="both" {{ old('garage_type')=='both'?'checked':'' }} class="mt-1" />
              <div class="min-w-0">
                <div class="font-semibold text-gray-900">Les deux</div>
                <div class="text-xs text-gray-600">Service complet</div>
              </div>
            </label>
          </div>
          @error('garage_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Known by --}}
        <div>
          <label for="known_by" class="mb-1 block text-sm font-semibold text-gray-800">Comment avez-vous connu GS Auto ?</label>
          <select id="known_by" name="known_by"
                  class="w-full appearance-none rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200">
            <option value="">Sélectionnez une option</option>
            <option value="site_web" {{ old('known_by')=='site_web'?'selected':'' }}>Site web</option>
            <option value="google" {{ old('known_by')=='google'?'selected':'' }}>Google</option>
            <option value="parrainage" {{ old('known_by')=='parrainage'?'selected':'' }}>Parrainage / Recommandation</option>
            <option value="evenements" {{ old('known_by')=='evenements'?'selected':'' }}>Évènements</option>
            <option value="prospection" {{ old('known_by')=='prospection'?'selected':'' }}>Prospection</option>
            <option value="chatgpt" {{ old('known_by')=='chatgpt'?'selected':'' }}>ChatGPT</option>
            <option value="reseaux_sociaux" {{ old('known_by')=='reseaux_sociaux'?'selected':'' }}>Réseaux sociaux</option>
            <option value="emailing" {{ old('known_by')=='emailing'?'selected':'' }}>E-mailing</option>
            <option value="autre" {{ old('known_by')=='autre'?'selected':'' }}>Autre</option>
          </select>
          @error('known_by') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Passwords --}}
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Mot de passe *</label>
            <input id="password" name="password" type="password" required
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Répéter le mot de passe *</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none transition focus:border-[#FF7A1C] focus:bg-white focus:ring-2 focus:ring-orange-200" />
            @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Terms --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4">
          <label class="flex items-start gap-3">
            <input type="checkbox" name="terms" value="1" required class="mt-1" />
            <span class="text-sm text-gray-700">
              J'accepte les
              <a href="https://gservicesauto.com/politique-confidentialite/" target="_blank" class="font-semibold text-[#FF4B00] underline">conditions générales d'utilisation</a>
              et la
              <a href="https://gservicesauto.com/mentions-legales/" target="_blank" class="font-semibold text-[#FF4B00] underline">politique de confidentialité</a>.
            </span>
          </label>
          @error('terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-[#FF4B00] to-[#FF7A1C] py-3.5 text-lg font-bold text-white shadow-md transition hover:brightness-105 active:scale-[0.99]">
          Créer mon compte
        </button>
      </form>

    </div>
  </div>
</div>
@endsection