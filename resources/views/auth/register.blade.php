@extends('layouts.guest')

@section('title', 'Création de compte')

@section('content')
<div class="w-full">
  {{-- Hero band --}}
  <div class="mx-auto mb-8 max-w-3xl rounded-2xl bg-gradient-to-r from-[#FF4B00] to-[#FF7A1C] px-6 py-6 text-white shadow-lg">
    <div class="flex items-center gap-3">
      <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-12 w-auto drop-shadow" />
      <div>
        <h1 class="text-xl font-extrabold leading-tight">Créer votre compte GS Auto</h1>
        <p class="text-white/90 text-sm">Démarrez en quelques minutes — sécurisé & professionnel.</p>
      </div>
    </div>
  </div>

  {{-- Card --}}
  <div class="mx-auto w-full max-w-4xl rounded-2xl bg-white px-6 py-8 shadow-lg">
    {{-- Success flash --}}
    @if (session('success'))
      <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
        {{ session('success') }}
      </div>
    @endif

    {{-- Global errors (top) --}}
    @if ($errors->any())
      <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('register') }}" novalidate>
      @csrf

      {{-- Basic info --}}
      <h2 class="mb-4 text-lg font-bold text-gray-900">Informations personnelles</h2>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label for="first_name" class="mb-1 block font-medium text-gray-700">Prénom *</label>
          <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}"
                 required placeholder="Votre prénom"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
          @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="last_name" class="mb-1 block font-medium text-gray-700">Nom *</label>
          <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}"
                 required placeholder="Votre nom"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
          @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
          <label for="email" class="mb-1 block font-medium text-gray-700">Adresse e-mail *</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}"
                 required placeholder="exemple@domaine.com"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
          @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Company --}}
      <h2 class="mt-8 mb-4 text-lg font-bold text-gray-900">Société</h2>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label for="company_name" class="mb-1 block font-medium text-gray-700">Nom de la société *</label>
          <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}"
                 required placeholder="Nom de votre société"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
          @error('company_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="commercial_name" class="mb-1 block font-medium text-gray-700">Nom commercial</label>
          <input id="commercial_name" name="commercial_name" type="text" value="{{ old('commercial_name') }}"
                 placeholder="Optionnel"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
          @error('commercial_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="phone" class="mb-1 block font-medium text-gray-700">Téléphone *</label>
          <input id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                 required placeholder="01 23 45 67 89"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
          @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="siret" class="mb-1 block font-medium text-gray-700">SIRET *</label>
          <input id="siret" name="siret" type="text" value="{{ old('siret') }}"
                 required placeholder="123 456 789 01234"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
          @error('siret') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
          <label for="tva" class="mb-1 block font-medium text-gray-700">Numéro de TVA *</label>
          <input id="tva" name="tva" type="text" value="{{ old('tva') }}"
                 required placeholder="FR 12 345678901"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
          @error('tva') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Garage type + discovery --}}
      <h2 class="mt-8 mb-3 text-lg font-bold text-gray-900">Votre activité</h2>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <span class="mb-2 block font-medium text-gray-700">Type de garage</span>
          <div class="grid gap-2">
            <label class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 hover:border-[#FF7A1C]">
              <input type="radio" name="garage_type" value="fixe" {{ old('garage_type') == 'fixe' ? 'checked' : '' }}/>
              <span>Fixe — sur place</span>
            </label>
            <label class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 hover:border-[#FF7A1C]">
              <input type="radio" name="garage_type" value="mobile" {{ old('garage_type') == 'mobile' ? 'checked' : '' }}/>
              <span>Mobile — chez le client</span>
            </label>
            <label class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 hover:border-[#FF7A1C]">
              <input type="radio" name="garage_type" value="both" {{ old('garage_type') == 'both' ? 'checked' : '' }}/>
              <span>Les deux</span>
            </label>
          </div>
          @error('garage_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="known_by" class="mb-2 block font-medium text-gray-700">Comment nous avez-vous connu ?</label>
          <select id="known_by" name="known_by"
                  class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200">
            <option value="">Sélectionnez une option</option>
            @foreach ([
              'site_web' => 'Site web',
              'google' => 'Google',
              'parrainage' => 'Parrainage / Recommandation',
              'evenements' => 'Évènements',
              'prospection' => 'Prospection',
              'chatgpt' => 'ChatGPT',
              'reseaux_sociaux' => 'Réseaux sociaux',
              'emailing' => 'E-mailing',
              'autre' => 'Autre',
            ] as $val => $label)
              <option value="{{ $val }}" @selected(old('known_by') == $val)>{{ $label }}</option>
            @endforeach
          </select>
          @error('known_by') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Passwords --}}
      <h2 class="mt-8 mb-3 text-lg font-bold text-gray-900">Sécurité du compte</h2>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label for="password" class="mb-1 block font-medium text-gray-700">Mot de passe *</label>
          <div class="relative flex items-center rounded-lg border border-gray-300 bg-gray-50 px-2">
            <input id="password" name="password" type="password" required placeholder="Mot de passe"
                   class="w-full bg-transparent py-2 pr-10 outline-none focus:ring-0"/>
            <button type="button"
                    class="absolute right-2 inline-flex items-center justify-center rounded p-1 text-gray-500 hover:text-gray-700"
                    aria-label="Afficher / masquer le mot de passe"
                    data-toggle="password"
                    data-target="#password">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
          <div id="pwdMeter" class="mt-2 h-1.5 w-full rounded bg-gray-200">
            <div id="pwdBar" class="h-full w-0 rounded bg-red-500 transition-[width,background-color]"></div>
          </div>
          <p id="pwdHint" class="mt-1 text-xs text-gray-500">8+ caractères, majuscules, minuscules, chiffres & symboles.</p>
          @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="password_confirmation" class="mb-1 block font-medium text-gray-700">Confirmation *</label>
          <div class="relative flex items-center rounded-lg border border-gray-300 bg-gray-50 px-2">
            <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Confirmez le mot de passe"
                   class="w-full bg-transparent py-2 pr-10 outline-none focus:ring-0"/>
            <button type="button"
                    class="absolute right-2 inline-flex items-center justify-center rounded p-1 text-gray-500 hover:text-gray-700"
                    aria-label="Afficher / masquer le mot de passe"
                    data-toggle="password"
                    data-target="#password_confirmation">
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
          @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Terms --}}
      <div class="mt-6">
        <label class="flex items-start gap-2 text-sm text-gray-700">
          <input type="checkbox" name="terms" value="1" required class="mt-1">
          <span>
            J’accepte les
            <a href="https://gservicesauto.com/politique-confidentialite/" target="_blank" class="font-semibold text-[#FF4B00] underline">conditions d’utilisation</a>
            et la
            <a href="https://gservicesauto.com/mentions-legales/" target="_blank" class="font-semibold text-[#FF4B00] underline">politique de confidentialité</a>.
          </span>
        </label>
        @error('terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- reCAPTCHA --}}
      <div class="mt-4">
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
        @error('g-recaptcha-response') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Submit --}}
      <div class="mt-6">
        <button type="submit"
                class="w-full rounded-lg bg-black py-2.5 text-lg font-bold text-white transition hover:bg-gray-800">
          Créer mon compte
        </button>
      </div>

      {{-- Link to login --}}
      <p class="mt-4 text-center text-sm text-gray-600">
        Déjà un compte ?
        <a href="{{ route('login') }}" class="font-semibold text-[#FF4B00] hover:underline">Se connecter</a>
      </p>
    </form>
  </div>
</div>

{{-- Password toggle + strength meter --}}
<script>
  // toggle visibility
  document.querySelectorAll('[data-toggle="password"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.querySelector(btn.dataset.target);
      if (!target) return;
      const isPwd = target.type === 'password';
      target.type = isPwd ? 'text' : 'password';
      const icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
        icon.classList.toggle('fa-regular');
        icon.classList.toggle('fa-solid');
      }
      target.focus();
    });
  });

  // strength meter (basic)
  const pwd = document.getElementById('password');
  const bar = document.getElementById('pwdBar');
  function score(s){
    let c = 0;
    if (!s) return 0;
    if (s.length >= 8) c++;
    if (/[A-Z]/.test(s)) c++;
    if (/[a-z]/.test(s)) c++;
    if (/\d/.test(s)) c++;
    if (/[^A-Za-z0-9]/.test(s)) c++;
    return Math.min(c, 5);
  }
  function render(v){
    const w = (v/5)*100;
    bar.style.width = w + '%';
    bar.style.backgroundColor = ['#ef4444','#f59e0b','#84cc16','#22c55e','#16a34a'][Math.max(0,v-1)] || '#ef4444';
  }
  if (pwd && bar){
    pwd.addEventListener('input', e => render(score(e.target.value)));
  }
</script>
@endsection