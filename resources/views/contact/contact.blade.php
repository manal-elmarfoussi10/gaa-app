@extends('layouts.guest')

@section('title', 'Contactez-nous — GS Auto')

@section('content')
<div class="fade-in">
    {{-- Logo --}}
    <div class="flex justify-center mb-10">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-28 w-auto drop-shadow-lg transition-transform hover:scale-105">
    </div>

    <h2 class="text-3xl font-bold text-center text-gray-900 mb-2">Contactez-nous</h2>
    <p class="text-gray-600 text-center mb-10">Nous sommes là pour vous aider. Envoyez-nous un message et nous vous répondrons rapidement.</p>

<div class="grid gap-8 md:grid-cols-[1.1fr_.9fr]">
  {{-- Left: The form card --}}
  <section class="rounded-3xl bg-white/90 p-6 shadow-card ring-1 ring-black/5 md:p-8">

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

    <form method="POST" action="{{ route('contact.send') }}" class="space-y-6" novalidate>
      @csrf
      <input type="hidden" name="type" value="general">

      <div>
        <label for="company_name" class="mb-1 block text-sm font-medium">Nom de l'entreprise (optionnel)</label>
        <input id="company_name" name="company_name" value="{{ old('company_name') }}"
               class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" placeholder="Nom de votre entreprise" />
        @error('company_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label for="name" class="mb-1 block text-sm font-medium">Nom complet *</label>
        <input id="name" name="name" value="{{ old('name') }}" required
               class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" placeholder="Votre nom complet" />
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label for="email" class="mb-1 block text-sm font-medium">Adresse e-mail *</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
               class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" placeholder="exemple@domaine.com" />
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label for="subject" class="mb-1 block text-sm font-medium">Objet *</label>
        <input id="subject" name="subject" value="{{ old('subject') }}" required
               class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" placeholder="Objet de votre message" />
        @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label for="message" class="mb-1 block text-sm font-medium">Message *</label>
        <textarea id="message" name="message" rows="4" required
                  class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-orange-200" placeholder="Décrivez votre demande en détail...">{{ old('message') }}</textarea>
        @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Submit --}}
      <button type="submit"
              class="w-full rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 py-3.5 text-lg font-bold text-white shadow-card transition hover:brightness-105 active:scale-[0.99]">
        Envoyer le message
      </button>
    </form>
  </section>

  {{-- Right: Brand panel / contact info --}}
  <aside class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-orange-400 p-1 shadow-card">
    <div class="relative h-full w-full rounded-[22px] bg-white/10 p-6 md:p-8">
      <div class="mb-6 flex items-center gap-3 text-white/90">
        <div class="grid h-10 w-10 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/20">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div>
          <div class="text-sm opacity-90">Contact GS Auto</div>
          <div class="text-xl font-extrabold tracking-tight">Informations</div>
        </div>
      </div>

      <ul class="space-y-4 text-white/95">
        <li class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
          <div class="font-semibold">Téléphone</div>
          <div class="text-sm opacity-90">01 84 80 68 32</div>
        </li>
        <li class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
          <div class="font-semibold">Email</div>
          <div class="text-sm opacity-90">contact@gagestion.fr</div>
        </li>
        <li class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
          <div class="font-semibold">Horaires</div>
          <div class="text-sm opacity-90">Lundi - Samedi, 9h - 18h</div>
        </li>
      </ul>

      <div class="mt-8 rounded-2xl bg-white/15 p-4 text-white/95 ring-1 ring-white/20">
        <div class="text-sm opacity-90">Support technique</div>
        <div class="text-lg font-bold">support@gagestion.fr</div>
        <div class="text-sm">Réponse sous 24h</div>
      </div>

      {{-- floating gloss --}}
      <div class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-white/20 blur-2xl"></div>
      <div class="pointer-events-none absolute -left-10 -bottom-10 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
    </div>
  </aside>
</div>
@endsection
