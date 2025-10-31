@extends('layouts.guest')

@section('title', 'Connexion')

@section('content')
<div class="fade-in">
    {{-- Logo --}}
    <div class="flex justify-center mb-10">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-28 w-auto drop-shadow-lg transition-transform hover:scale-105">
    </div>

    <h2 class="text-3xl font-bold text-center text-gray-900 mb-2">Connexion à votre compte</h2>
    <p class="text-gray-600 text-center mb-10">Connectez-vous pour accéder à la plateforme.</p>

    {{-- Generic error banner --}}
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 animate-pulse">
            @if($errors->has('email'))
                {{ $errors->first('email') }}
            @elseif($errors->has('password'))
                {{ $errors->first('password') }}
            @else
                Identifiants invalides.
            @endif
        </div>
    @endif

    {{-- Legacy custom error message --}}
    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 animate-pulse">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate class="space-y-6">
        @csrf

        {{-- Email --}}
        <div class="relative">
            <label for="email" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Adresse e-mail</label>
            <div class="flex items-center rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus-within:border-orange-400 transition-all duration-200">
                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="username"
                       placeholder=" "
                       class="w-full bg-transparent py-1 outline-none peer">
            </div>
            @error('email') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Password --}}
        <div class="relative">
            <label for="password" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Mot de passe</label>
            <div class="relative flex items-center rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus-within:border-orange-400 transition-all duration-200">
                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3 1.343 3 3v1H9v-1c0-1.657 1.343-3 3-3zm-4 4v2a2 2 0 002 2h4a2 2 0 002-2v-2m-8 0h8"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 018 0v4"/>
                </svg>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder=" "
                       class="w-full bg-transparent py-1 pr-10 outline-none peer">
                <button type="button"
                        class="absolute right-3 inline-flex items-center justify-center rounded p-1 text-gray-500 hover:text-orange-600 transition-colors"
                        aria-label="Afficher / masquer le mot de passe"
                        data-toggle="password"
                        data-target="#password">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
            <p id="capsLockHint" class="mt-2 hidden text-xs text-amber-600">Attention : Verr. Maj activée</p>
            @error('password') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Options --}}
        <div class="flex items-center justify-between">
            <label class="inline-flex items-center text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" name="remember" @checked(old('remember')) class="mr-3 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 focus:ring-2">
                Se souvenir de moi
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors">
                Mot de passe oublié ?
            </a>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full rounded-xl bg-black py-3 text-lg font-bold text-white transition-all duration-200 hover:bg-gray-900 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
            Se connecter
        </button>
    </form>

    {{-- Register link --}}
    <div class="text-center mt-8">
        <span class="text-sm text-gray-600">Pas de compte ?</span>
        <a href="{{ route('register') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors ml-1">S'inscrire</a>
    </div>
</div>

{{-- Password toggle + Caps Lock hint --}}
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

  // caps lock warning
  const pwd = document.getElementById('password');
  const capsHint = document.getElementById('capsLockHint');
  function setCaps(ev) {
    if (!capsHint) return;
    const isOn = ev.getModifierState && ev.getModifierState('CapsLock');
    capsHint.classList.toggle('hidden', !isOn);
  }
  ['keydown','keyup','focus'].forEach(ev => pwd.addEventListener(ev, setCaps));
</script>
@endsection
