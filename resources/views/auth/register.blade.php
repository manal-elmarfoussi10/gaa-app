@extends('layouts.guest')
@section('title', 'Créer un compte — GS Auto')


@section('content')
<div class="fade-in">
    {{-- Logo --}}
    <div class="flex justify-center mb-10">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-28 w-auto drop-shadow-lg transition-transform hover:scale-105">
    </div>

    <h2 class="text-3xl font-bold text-center text-gray-900 mb-2">Création de votre compte</h2>
    <p class="text-gray-600 text-center mb-10">Rejoignez GS Auto et accédez à la gestion de vos dossiers bris de glace.</p>

<div class="grid gap-8 md:grid-cols-[1.1fr_.9fr]">
  {{-- Left: The form card --}}
  <section class="rounded-3xl bg-white/90 p-6 shadow-card ring-1 ring-black/5 md:p-8">
    <header class="mb-6">
      <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Création de votre compte</h1>
      <p class="mt-1 text-sm text-gray-600">Rejoignez GS Auto et accédez à la gestion de vos dossiers bris de glace.</p>
    </header>

    {{-- Alerts --}}
    @if(session('success'))
      <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
        {{ session('success') }}
      </div>
    @endif
    @if($errors->any())
      <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-6" novalidate>
      @csrf

      {{-- Identity --}}
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label for="first_name" class="mb-1 block text-sm font-medium">Prénom *</label>
          <input id="first_name" name="first_name" value="{{ old('first_name') }}" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label for="last_name" class="mb-1 block text-sm font-medium">Nom *</label>
          <input id="last_name" name="last_name" value="{{ old('last_name') }}" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div>
        <label for="email" class="mb-1 block text-sm font-medium">Adresse e-mail *</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
               class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Company --}}
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label for="company_name" class="mb-1 block text-sm font-medium">Nom de la société *</label>
          <input id="company_name" name="company_name" value="{{ old('company_name') }}" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('company_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label for="commercial_name" class="mb-1 block text-sm font-medium">Nom commercial</label>
          <input id="commercial_name" name="commercial_name" value="{{ old('commercial_name') }}"
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('commercial_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-3">
        <div>
          <label for="phone" class="mb-1 block text-sm font-medium">Téléphone *</label>
          <input id="phone" name="phone" value="{{ old('phone') }}" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label for="siret" class="mb-1 block text-sm font-medium">SIRET *</label>
          <input id="siret" name="siret" value="{{ old('siret') }}" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('siret') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label for="tva" class="mb-1 block text-sm font-medium">TVA *</label>
          <input id="tva" name="tva" value="{{ old('tva') }}" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('tva') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Garage type (animated selection) --}}
      <div>
        <div class="mb-2 text-sm font-semibold">Quel type de garage avez-vous ?</div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          @php $gt = old('garage_type'); @endphp
          @foreach ([
            ['fixe','Fixe','Changement sur site'],
            ['mobile','Mobile','Déplacement chez le client'],
            ['both','Les deux','Service complet'],
          ] as [$val,$title,$desc])
          <label class="group relative flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-soft transition hover:border-brand-600 hover:shadow-card">
            <input type="radio" name="garage_type" value="{{ $val }}" {{ $gt===$val ? 'checked' : '' }} class="mt-1">
            <div class="min-w-0">
              <div class="font-semibold text-gray-900">{{ $title }}</div>
              <div class="text-xs text-gray-600">{{ $desc }}</div>
            </div>
            <span class="pointer-events-none absolute inset-0 rounded-xl ring-2 ring-transparent group-hover:ring-brand-600/20"></span>
          </label>
          @endforeach
        </div>
        @error('garage_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Known by --}}
      <div>
        <label for="known_by" class="mb-1 block text-sm font-semibold">Comment avez-vous connu GS Auto ?</label>
        <select id="known_by" name="known_by"
                class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200">
          @php $kb=old('known_by'); @endphp
          <option value="">Sélectionnez une option</option>
          @foreach ([
            'site_web'=>'Site web','google'=>'Google','parrainage'=>'Parrainage / Recommandation',
            'evenements'=>'Évènements','prospection'=>'Prospection','chatgpt'=>'ChatGPT',
            'reseaux_sociaux'=>'Réseaux sociaux','emailing'=>'E-mailing','autre'=>'Autre'
          ] as $v=>$t)
            <option value="{{ $v }}" {{ $kb===$v ? 'selected':'' }}>{{ $t }}</option>
          @endforeach
        </select>
        @error('known_by') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Passwords --}}
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label for="password" class="mb-1 block text-sm font-medium">Mot de passe *</label>
          <input id="password" name="password" type="password" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirmer le mot de passe *</label>
          <input id="password_confirmation" name="password_confirmation" type="password" required
                 class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" />
          @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Terms --}}
      <div class="rounded-xl border border-gray-200 bg-white p-4">
        <label class="flex items-start gap-3">
          <input type="checkbox" name="terms" value="1" required class="mt-1" />
          <span class="text-sm text-gray-700">
            J'accepte les
            <a class="font-semibold text-brand-600 underline" href="https://gservicesauto.com/politique-confidentialite/" target="_blank">conditions générales</a>
            et la
            <a class="font-semibold text-brand-600 underline" href="https://gservicesauto.com/mentions-legales/" target="_blank">politique de confidentialité</a>.
          </span>
        </label>
        @error('terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Submit --}}
      <button type="submit"
              class="w-full rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 py-3.5 text-lg font-bold text-white shadow-card transition hover:brightness-105 active:scale-[0.99]">
        Créer mon compte
      </button>

      <p class="text-center text-sm text-gray-600">Déjà un compte ?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Se connecter</a>
      </p>
    </form>
  </section>

  {{-- Right: Brand panel / benefits (nice motion) --}}
  <aside class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-orange-400 p-1 shadow-card">
    <div class="relative h-full w-full rounded-[22px] bg-white/10 p-6 md:p-8">
      <div class="mb-6 flex items-center gap-3 text-white/90">
        <div class="grid h-10 w-10 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/20">
          <!-- small icon -->
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M4 7h16M4 12h16M4 17h10" stroke="white" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <div>
          <div class="text-sm opacity-90">Plateforme GS Auto</div>
          <div class="text-xl font-extrabold tracking-tight">Pourquoi nous choisir ?</div>
        </div>
      </div>

      <ul class="space-y-4 text-white/95">
        @foreach ([
          ['Gestion centralisée','Dossiers, clients, interventions & facturation.'],
          ['Gains de temps','De la déclaration au règlement, tout est fluide.'],
          ['Sécurité & conformité','Hébergement EU, e-signature, sauvegardes.'],
          ['Support 24/7','Équipe réactive et documentation claire.'],
        ] as [$t,$d])
        <li class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 transition hover:bg-white/15">
          <div class="font-semibold">{{ $t }}</div>
          <div class="text-sm opacity-90">{{ $d }}</div>
        </li>
        @endforeach
      </ul>

      <div class="mt-8 rounded-2xl bg-white/15 p-4 text-white/95 ring-1 ring-white/20">
        <div class="text-sm opacity-90">Besoin d’une démo ?</div>
        <div class="text-lg font-bold">contact@gagestion.fr</div>
        <div class="text-sm">01 84 80 68 32</div>
      </div>

      {{-- floating gloss --}}
      <div class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-white/20 blur-2xl"></div>
      <div class="pointer-events-none absolute -left-10 -bottom-10 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
    </div>
  </aside>
</div>
@endsection