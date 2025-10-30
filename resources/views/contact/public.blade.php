@extends('layouts.guest')

@section('title', 'Contact')

@section('content')
<div class="w-full">
  {{-- Hero band (brand gradient) --}}
  <div class="mx-auto mb-8 max-w-3xl rounded-2xl bg-gradient-to-r from-[#FF4B00] to-[#FF7A1C] px-6 py-6 text-white shadow-lg">
    <div class="flex items-center gap-3">
      <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-12 w-auto drop-shadow" />
      <div>
        <h1 class="text-xl font-extrabold leading-tight">Contactez GS Auto</h1>
        <p class="text-white/90 text-sm">Expliquez-nous votre besoin bris de glace — réponse rapide et personnalisée.</p>
      </div>
    </div>
  </div>

  {{-- Card --}}
  <div class="mx-auto w-full max-w-5xl rounded-2xl bg-white px-6 py-8 shadow-lg">
    {{-- Success flash --}}
    @if (session('success'))
      <div id="flash-success" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
        {{ session('success') }}
      </div>
    @endif

    {{-- Global errors --}}
    @if ($errors->any())
      <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="grid gap-8 md:grid-cols-2">
      {{-- LEFT: Form --}}
      <section>
        <h2 class="mb-1 text-lg font-bold text-gray-900">Envoyez-nous un message</h2>
        <p class="mb-5 text-sm text-gray-500">Nous vous répondrons très rapidement.</p>

        <form method="POST" action="{{ route('contact.send') }}" novalidate class="space-y-4">
          @csrf

          {{-- Objet (tiles) --}}
          <div>
            <span class="mb-2 block font-medium text-gray-700">Objet de votre demande *</span>
            <div class="grid gap-2 sm:grid-cols-3">
              <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 hover:border-[#FF7A1C]">
                <input type="radio" name="type" value="general" class="mt-1"
                       {{ old('type','general') === 'general' ? 'checked' : '' }} required />
                <div>
                  <strong class="block text-gray-900">Contact général</strong>
                  <span class="text-xs text-gray-500">Questions, support, informations</span>
                </div>
              </label>
              <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 hover:border-[#FF7A1C]">
                <input type="radio" name="type" value="demo" class="mt-1"
                       {{ old('type') === 'demo' ? 'checked' : '' }} />
                <div>
                  <strong class="block text-gray-900">Demande de démo</strong>
                  <span class="text-xs text-gray-500">Présentation en direct</span>
                </div>
              </label>
              <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 hover:border-[#FF7A1C]">
                <input type="radio" name="type" value="partner" class="mt-1"
                       {{ old('type') === 'partner' ? 'checked' : '' }} />
                <div>
                  <strong class="block text-gray-900">Devenir partenaire</strong>
                  <span class="text-xs text-gray-500">Intégrations & distribution</span>
                </div>
              </label>
            </div>
            @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Nom --}}
          <div>
            <label for="name" class="mb-1 block font-medium text-gray-700">Nom complet *</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                   placeholder="Votre nom complet"
                   class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div>
            <label for="email" class="mb-1 block font-medium text-gray-700">Adresse e-mail *</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   placeholder="exemple@domaine.com"
                   class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Message --}}
          <div>
            <label for="message" class="mb-1 block font-medium text-gray-700">Message *</label>
            <textarea id="message" name="message" rows="5" required
                      placeholder="Décrivez votre besoin (volume de dossiers, nombre d’agences, outils actuels…)"
                      class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:border-[#FF7A1C] focus:ring-2 focus:ring-orange-200">{{ old('message') }}</textarea>
            @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- reCAPTCHA --}}
          <div>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
            @error('g-recaptcha-response') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Actions --}}
          <div class="pt-2">
            <button type="submit"
                    class="w-full rounded-lg bg-black py-2.5 text-lg font-bold text-white transition hover:bg-gray-800">
              Envoyer le message
            </button>
            <div class="mt-3 flex flex-wrap items-center justify-center gap-3 text-sm">
              <a href="tel:+33184806832" class="font-semibold text-[#FF4B00] hover:underline">Appeler</a>
              <span class="text-gray-300">•</span>
              <a href="mailto:contact@gagestion.fr" class="font-semibold text-[#FF4B00] hover:underline">Écrire un email</a>
            </div>
          </div>
        </form>
      </section>

      {{-- RIGHT: Contact info (matching card) --}}
      <aside class="space-y-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="mb-3 flex items-center gap-2">
            <div class="h-9 w-9 rounded-lg bg-gradient-to-r from-[#FF4B00] to-[#FF7A1C] text-white grid place-items-center font-bold shadow-md">i</div>
            <div>
              <div class="font-extrabold text-gray-900">Informations de contact</div>
              <div class="text-xs text-gray-500">Une équipe à votre écoute</div>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <div class="rounded-lg border border-gray-200 bg-white p-3 text-center">
              <div class="text-lg font-bold text-gray-900">24h</div>
              <div class="text-xs text-gray-500">Réponse moyenne</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3 text-center">
              <div class="text-lg font-bold text-gray-900">98%</div>
              <div class="text-xs text-gray-500">Satisfaction</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3 text-center">
              <div class="text-lg font-bold text-gray-900">EU</div>
              <div class="text-xs text-gray-500">Données hébergées</div>
            </div>
          </div>

          <div class="mt-4 space-y-3">
            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3">
              <div class="grid h-9 w-9 place-items-center rounded-lg bg-gradient-to-r from-[#FF4B00] to-[#FF7A1C] font-bold text-white shadow-md">☎</div>
              <div>
                <div class="font-semibold text-gray-900">Téléphone</div>
                <a href="tel:+33184806832" class="text-sm font-semibold text-[#FF4B00] hover:underline">01 84 80 68 32</a>
              </div>
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3">
              <div class="grid h-9 w-9 place-items-center rounded-lg bg-gradient-to-r from-[#FF4B00] to-[#FF7A1C] font-bold text-white shadow-md">✉</div>
              <div>
                <div class="font-semibold text-gray-900">Email</div>
                <a href="mailto:contact@gagestion.fr" class="text-sm font-semibold text-[#FF4B00] hover:underline">contact@gagestion.fr</a>
              </div>
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3">
              <div class="grid h-9 w-9 place-items-center rounded-lg bg-gradient-to-r from-[#FF4B00] to-[#FF7A1C] font-bold text-white shadow-md">⏱</div>
              <div>
                <div class="font-semibold text-gray-900">Horaires</div>
                <div class="text-sm text-gray-600">Lundi – Samedi • 9h – 18h</div>
              </div>
            </div>

            <div class="mt-1 flex flex-wrap gap-2">
              <span class="inline-flex items-center gap-2 rounded-full border border-dashed border-gray-300 bg-white px-3 py-1 text-xs text-gray-600">Sécurité renforcée</span>
              <span class="inline-flex items-center gap-2 rounded-full border border-dashed border-gray-300 bg-white px-3 py-1 text-xs text-gray-600">E-signature</span>
              <span class="inline-flex items-center gap-2 rounded-full border border-dashed border-gray-300 bg-white px-3 py-1 text-xs text-gray-600">API & Webhooks</span>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</div>

{{-- Success toast (auto-hide) --}}
@if (session('success'))
  <div id="toast"
       class="fixed bottom-5 right-5 z-50 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900 shadow-xl opacity-0 translate-y-3 transition-all">
    <span class="rounded-full border border-emerald-200 bg-white px-2 py-0.5 text-xs font-semibold text-emerald-700">✔</span>
    <span class="text-sm font-semibold">Message envoyé.</span>
    <span class="text-sm text-emerald-700">Nous revenons vers vous très vite.</span>
  </div>
  <script>
    const t = document.getElementById('toast');
    const f = document.getElementById('flash-success');
    setTimeout(()=> t?.classList.remove('opacity-0','translate-y-3'), 120);
    setTimeout(()=> { t?.classList.add('opacity-0','translate-y-3'); f?.remove(); }, 4200);
  </script>
@endif
@endsection