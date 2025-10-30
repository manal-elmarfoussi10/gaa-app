@extends('layouts.guest')

@section('title', 'Connexion')

@section('content')

<div class="bg-white rounded-2xl shadow-lg px-8 py-10 max-w-lg w-full">
    {{-- Logo --}}
    <div class="flex justify-center mb-8">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-24 w-auto">
    </div>
  

    <h2 class="text-2xl font-bold text-center text-gray-900 mb-1">Connexion à votre compte</h2>
    <p class="text-gray-500 text-center mb-8">Connectez-vous pour accéder à la plateform.</p>

    {{-- Generic error banner (covers Breeze/Fortify default errors) --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
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
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-5">
            <label for="email" class="block mb-1 font-medium text-gray-700">Adresse e-mail</label>
            <div class="flex items-center rounded border border-gray-300 bg-gray-50 px-2">
                <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="username"
                       placeholder="exemple@domaine.com"
                       class="w-full bg-transparent py-2 outline-none focus:ring-0">
            </div>
            @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        {{-- Password --}}
        <div class="mb-2">
            <label for="password" class="block mb-1 font-medium text-gray-700">Mot de passe</label>
            <div class="relative flex items-center rounded border border-gray-300 bg-gray-50 px-2">
                <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 11c1.657 0 3 1.343 3 3v1H9v-1c0-1.657 1.343-3 3-3zm-4 4v2a2 2 0 002 2h4a2 2 0 002-2v-2m-8 0h8"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 11V7a4 4 0 018 0v4"/>
                </svg>

                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Mot de passe"
                       class="w-full bg-transparent py-2 pr-10 outline-none focus:ring-0">

                {{-- eye button --}}
                <button type="button"
                        class="absolute right-2 inline-flex items-center justify-center rounded p-1 text-gray-500 hover:text-gray-700"
                        aria-label="Afficher / masquer le mot de passe"
                        data-toggle="password"
                        data-target="#password">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
            <p id="capsLockHint" class="mt-1 hidden text-xs text-amber-600">Attention : Verr. Maj activée</p>
            @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        {{-- Options --}}
        <div class="mb-6 mt-2 flex items-center justify-between">
            <label class="inline-flex items-center text-sm text-gray-600">
                <input type="checkbox" name="remember" @checked(old('remember')) class="mr-2">
                Se souvenir de moi
            </label>
            <a href="{{ route('password.request') }}"
               class="text-sm font-semibold text-blue-600 hover:underline">
                Mot de passe oublié ?
            </a>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full rounded-lg bg-black py-2 text-lg font-bold text-white transition hover:bg-gray-800">
            Se connecter
        </button>
    </form>

    {{-- Register link --}}
    <div class="text-center mt-4">
        <span class="text-sm text-gray-600">Pas de compte ?</span>
        <a href="{{ route('register') }}" class="text-sm font-semibold text-blue-600 hover:underline ml-1">S'inscrire</a>
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