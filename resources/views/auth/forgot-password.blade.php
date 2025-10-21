@extends('layouts.guest')
@section('title', 'Mot de passe oublié')

@section('content')
<div class="bg-white rounded-2xl shadow-lg px-8 py-10 max-w-lg w-full">
    {{-- Logo --}}
    <div class="flex justify-center mb-8">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-24 w-auto">
    </div>

    <h2 class="text-2xl font-bold text-center text-gray-900 mb-1">Mot de passe oublié</h2>
    <p class="text-gray-500 text-center mb-8">Entrez votre e-mail pour recevoir un lien de réinitialisation.</p>

    {{-- Success/info --}}
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- Errors --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-5">
            <label for="email" class="block mb-1 font-medium text-gray-700">Adresse e-mail</label>
            <div class="flex items-center rounded border border-gray-300 bg-gray-50 px-2">
                <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="exemple@domaine.com"
                       class="w-full bg-transparent py-2 outline-none focus:ring-0">
            </div>
            @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full rounded-lg bg-black py-2 text-lg font-bold text-white transition hover:bg-gray-800">
            Envoyer le lien
        </button>
    </form>

    <a href="{{ route('login') }}"
       class="block text-center mt-4 text-sm font-semibold text-blue-600 hover:underline">
        Retour à la connexion
    </a>
</div>
@endsection