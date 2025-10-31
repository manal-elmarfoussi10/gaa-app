@extends('layouts.guest')

@section('title', 'Inscription réussie — GS Auto')

@section('content')
{{-- Logo --}}
<div class="flex justify-center mb-10">
    <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-28 w-auto drop-shadow-lg transition-transform hover:scale-105">
</div>

<div class="grid gap-8 md:grid-cols-[1.1fr_.9fr]">
  {{-- Left: The form card --}}
  <section class="rounded-3xl bg-white/90 p-6 shadow-card ring-1 ring-black/5 md:p-8">
    <header class="mb-6">
     
      <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Inscription réussie !</h1>
      <p class="mt-1 text-sm text-gray-600">Votre compte a été créé avec succès.</p>
    </header>

    {{-- Success message --}}
    <div class="text-center">
      <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
        <p class="font-semibold">Félicitations !</p>
        <p>Votre compte administrateur a été créé avec succès. Un email de vérification a été envoyé à votre adresse email.</p>
        <p class="mt-2 text-sm">Vérifiez votre boîte de réception (ou le dossier spam) et cliquez sur le lien pour vérifier votre compte.</p>
      </div>

      <div class="space-y-4">
        <a href="{{ route('verification.notice') }}" class="inline-flex items-center rounded-xl bg-orange-600 px-6 py-3 text-lg font-bold text-white transition-all duration-200 hover:bg-orange-700 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
          Aller à la vérification d'email
        </a>

        <div class="text-center">
          <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">
            Retour à la connexion
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- Right: Brand panel --}}
  <aside class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-orange-400 p-1 shadow-card">
    <div class="relative h-full w-full rounded-[22px] bg-white/10 p-6 md:p-8">
      <div class="mb-6 flex items-center gap-3 text-white/90">
        <div class="grid h-10 w-10 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/20">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div>
          <div class="text-sm opacity-90">Prochaine étape</div>
          <div class="text-xl font-extrabold tracking-tight">Vérification d'email</div>
        </div>
      </div>

      <ul class="space-y-4 text-white/95">
        <li class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
          <div class="font-semibold">1. Vérifiez vos emails</div>
          <div class="text-sm opacity-90">Cliquez sur le lien de vérification envoyé à votre adresse email.</div>
        </li>
        <li class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
          <div class="font-semibold">2. Validation du compte</div>
          <div class="text-sm opacity-90">Votre email sera vérifié et votre compte prêt pour activation.</div>
        </li>
        <li class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
          <div class="font-semibold">3. Activation par l'admin</div>
          <div class="text-sm opacity-90">Un super administrateur examinera et activera votre compte.</div>
        </li>
      </ul>

      <div class="mt-8 rounded-2xl bg-white/15 p-4 text-white/95 ring-1 ring-white/20">
        <div class="text-sm opacity-90">Besoin d'aide ?</div>
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
