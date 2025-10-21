@extends('layouts.guest')
@section('title', 'Réinitialiser le mot de passe')

@section('content')
<div class="bg-white rounded-2xl shadow-lg px-8 py-10 max-w-lg w-full">
    {{-- Logo --}}
    <div class="flex justify-center mb-8">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-24 w-auto">
    </div>

    <h2 class="text-2xl font-bold text-center text-gray-900 mb-1">
        Réinitialiser le mot de passe
    </h2>
    <p class="text-gray-500 text-center mb-8">
        Saisissez votre e-mail et un nouveau mot de passe.
    </p>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        {{-- Hidden token --}}
        <input type="hidden" name="token"
               value="{{ old('token', $token ?? request()->route('token')) }}">

        {{-- Email --}}
        <div class="mb-5">
            <label for="email" class="block mb-1 font-medium text-gray-700">Adresse e-mail</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email', $email ?? request('email')) }}" required
                   class="w-full rounded border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:ring-0">
            @error('email')
                <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-5">
            <label for="password" class="block mb-1 font-medium text-gray-700">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" required
                   class="w-full rounded border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:ring-0">
            @error('password')
                <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        {{-- Confirm --}}
        <div class="mb-8">
            <label for="password_confirmation" class="block mb-1 font-medium text-gray-700">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="w-full rounded border border-gray-300 bg-gray-50 px-3 py-2 outline-none focus:ring-0">
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full rounded-lg bg-black py-2 text-lg font-bold text-white transition hover:bg-gray-800">
            Mettre à jour
        </button>
    </form>

    <a href="{{ route('login') }}"
       class="block text-center mt-4 text-sm font-semibold text-blue-600 hover:underline">
        Retour à la connexion
    </a>
</div>
@endsection