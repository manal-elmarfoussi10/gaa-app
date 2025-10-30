@extends('layouts.guest')

@section('title', 'Contact')

@section('content')
<div class="w-full fade-in">
  {{-- Hero band (brand gradient) --}}
  <div class="mx-auto mb-8 max-w-4xl rounded-2xl bg-gradient-to-r from-[#FF4B00] to-[#FF7A1C] px-8 py-8 text-white shadow-2xl animate-pulse">
    <div class="flex items-center gap-4">
      <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-16 w-auto drop-shadow-lg transition-transform hover:scale-105" />
      <div>
        <h1 class="text-2xl font-extrabold leading-tight">Contactez GS Auto</h1>
        <p class="text-white/90 text-base">Expliquez-nous votre besoin bris de glace — réponse rapide et personnalisée.</p>
      </div>
    </div>
  </div>

  {{-- Card --}}
  <div class="mx-auto w-full max-w-6xl rounded-2xl bg-white px-8 py-10 shadow-2xl border border-gray-100">
    {{-- Success flash --}}
    @if (session('success'))
      <div id="flash-success" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 animate-pulse">
        {{ session('success') }}
      </div>
    @endif

    {{-- Global errors --}}
    @if ($errors->any())
      <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 animate-pulse">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="grid gap-10 lg:grid-cols-2">
      {{-- LEFT: Form --}}
      <section class="animate-fade-in">
        <h2 class="mb-2 text-2xl font-bold text-gray-900">Envoyez-nous un message</h2>
        <p class="mb-6 text-base text-gray-600">Nous vous répondrons très rapidement.</p>

        <form method="POST" action="{{ route('contact.send') }}" novalidate class="space-y-5">
          @csrf

          {{-- Objet (tiles) --}}
          <div>
            <span class="mb-3 block text-base font-semibold text-gray-800">Objet de votre demande *</span>
            <div class="grid gap-3 sm:grid-cols-3">
              <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-gray-200 bg-white px-4 py-4 hover:border-orange-400 hover:shadow-md transition-all duration-200">
                <input type="radio" name="type" value="general" class="mt-1 w-4 h-4 text-orange-600 focus:ring-orange-500"
                       {{ old('type','general') === 'general' ? 'checked' : '' }} required />
                <div>
                  <strong class="block text-gray-900">Contact général</strong>
                  <span class="text-sm text-gray-600">Questions, support, informations</span>
                </div>
              </label>
              <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-gray-200 bg-white px-4 py-4 hover:border-orange-400 hover:shadow-md transition-all duration-200">
                <input type="radio" name="type" value="demo" class="mt-1 w-4 h-4 text-orange-600 focus:ring-orange-500"
                       {{ old('type') === 'demo' ? 'checked' : '' }} />
                <div>
                  <strong class="block text-gray-900">Demande de démo</strong>
                  <span class="text-sm text-gray-600">Présentation en direct</span>
                </div>
              </label>
              <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-gray-200 bg-white px-4 py-4 hover:border-orange-400 hover:shadow-md transition-all duration-200">
                <input type="radio" name="type" value="partner" class="mt-1 w-4 h-4 text-orange-600 focus:ring-orange-500"
                       {{ old('type') === 'partner' ? 'checked' : '' }} />
                <div>
                  <strong class="block text-gray-900">Devenir partenaire</strong>
                  <span class="text-sm text-gray-600">Intégrations & distribution</span>
                </div>
              </label>
            </div>
            @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Nom --}}
          <div class="relative">
            <label for="name" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Nom complet *</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder=" "
                   class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer" />
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div class="relative">
            <label for="email" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Adresse e-mail *</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder=" "
                   class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Message --}}
          <div class="relative">
            <label for="message" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Message *</label>
            <textarea id="message" name="message" rows="5" required placeholder=" "
                      class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer resize-none">{{ old('message') }}</textarea>
            @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- reCAPTCHA --}}
          <div>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
            @error('g-recaptcha-response') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Actions --}}
          <div class="pt-4">
            <button type="submit"
                    class="w-full rounded-xl bg-black py-3 text-lg font-bold text-white transition-all duration-200 hover:bg-gray-900 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
              Envoyer le message
            </button>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-base">
              <a href="tel:+33184806832" class="font-semibold text-orange-600 hover:text-orange-700 transition-colors">Appeler</a>
              <span class="text-gray-400">•</span>
              <a href="mailto:contact@gagestion.fr" class="font-semibold text-orange-600 hover:text-orange-700 transition-colors">Écrire un email</a>
            </div>
          </div>
        </form>
      </section>

      {{-- RIGHT: Contact info (matching card) --}}
      <aside class="space-y-6 animate-fade-in">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-6 shadow-xl">
          <div class="mb-4 flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 text-white grid place-items-center font-bold shadow-lg">i</div>
            <div>
              <div class="text-xl font-extrabold text-gray-900">Informations de contact</div>
              <div class="text-sm text-gray-600">Une équipe à votre écoute</div>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div class="rounded-xl border-2 border-gray-100 bg-gradient-to-br from-white to-gray-50 p-4 text-center shadow-sm hover:shadow-md transition-shadow">
              <div class="text-xl font-bold text-orange-600">24h</div>
              <div class="text-sm text-gray-600">Réponse moyenne</div>
            </div>
            <div class="rounded-xl border-2 border-gray-100 bg-gradient-to-br from-white to-gray-50 p-4 text-center shadow-sm hover:shadow-md transition-shadow">
              <div class="text-xl font-bold text-orange-600">98%</div>
              <div class="text-sm text-gray-600">Satisfaction</div>
            </div>
            <div class="rounded-xl border-2 border-gray-100 bg-gradient-to-br from-white to-gray-50 p-4 text-center shadow-sm hover:shadow-md transition-shadow">
              <div class="text-xl font-bold text-orange-600">EU</div>
              <div class="text-sm text-gray-600">Données hébergées</div>
            </div>
          </div>

          <div class="mt-6 space-y-4">
            <div class="flex items-center gap-4 rounded-xl border-2 border-gray-100 bg-gradient-to-r from-white to-gray-50 p-4 hover:shadow-md transition-all duration-200">
              <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 font-bold text-white shadow-lg">☎</div>
              <div>
                <div class="font-semibold text-gray-900">Téléphone</div>
                <a href="tel:+33184806832" class="text-base font-semibold text-orange-600 hover:text-orange-700 transition-colors">01 84 80 68 32</a>
              </div>
            </div>

            <div class="flex items-center gap-4 rounded-xl border-2 border-gray-100 bg-gradient-to-r from-white to-gray-50 p-4 hover:shadow-md transition-all duration-200">
              <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 font-bold text-white shadow-lg">✉</div>
              <div>
                <div class="font-semibold text-gray-900">Email</div>
                <a href="mailto:contact@gagestion.fr" class="text-base font-semibold text-orange-600 hover:text-orange-700 transition-colors">contact@gagestion.fr</a>
              </div>
            </div>

            <div class="flex items-center gap-4 rounded-xl border-2 border-gray-100 bg-gradient-to-r from-white to-gray-50 p-4 hover:shadow-md transition-all duration-200">
              <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 font-bold text-white shadow-lg">⏱</div>
              <div>
                <div class="font-semibold text-gray-900">Horaires</div>
                <div class="text-base text-gray-600">Lundi – Samedi • 9h – 18h</div>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
              <span class="inline-flex items-center gap-2 rounded-full border-2 border-dashed border-orange-200 bg-orange-50 px-4 py-2 text-sm text-orange-700 font-medium">Sécurité renforcée</span>
              <span class="inline-flex items-center gap-2 rounded-full border-2 border-dashed border-orange-200 bg-orange-50 px-4 py-2 text-sm text-orange-700 font-medium">E-signature</span>
              <span class="inline-flex items-center gap-2 rounded-full border-2 border-dashed border-orange-200 bg-orange-50 px-4 py-2 text-sm text-orange-700 font-medium">API & Webhooks</span>
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