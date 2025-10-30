@extends('layouts.guest')

@section('title', 'Vérification Email')

@section('content')
<div class="fade-in">
    {{-- Logo --}}
    <div class="flex justify-center mb-8">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-24 w-auto drop-shadow-lg transition-transform hover:scale-105">
    </div>

    <h2 class="text-3xl font-bold text-center text-gray-900 mb-2">Vérifiez votre email</h2>
    <p class="text-gray-600 text-center mb-8">Un code de vérification a été envoyé à votre adresse email.</p>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 animate-pulse">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.verify') }}" class="space-y-6" novalidate>
        @csrf

        {{-- Email (read-only) --}}
        <div class="relative">
            <label for="email" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left">Adresse email</label>
            <input id="email" type="email" value="{{ old('email', session('email')) }}" readonly class="w-full rounded-xl border-2 border-gray-300 bg-gray-100 px-4 py-3 text-gray-600 cursor-not-allowed">
        </div>

        {{-- Verification Code --}}
        <div class="relative">
            <label for="code" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Code de vérification *</label>
            <input id="code" type="text" name="code" value="{{ old('code') }}" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer" maxlength="6">
            @error('code') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="w-full rounded-xl bg-black py-3 text-lg font-bold text-white transition-all duration-200 hover:bg-gray-900 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
            Vérifier
        </button>
    </form>

    {{-- Resend Code --}}
    <div class="text-center mt-6">
        <p class="text-sm text-gray-600 mb-2">Vous n'avez pas reçu le code ?</p>
        <form method="POST" action="{{ route('verification.resend') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors">
                Renvoyer le code
            </button>
        </form>
    </div>

    {{-- Back to Register --}}
    <div class="text-center mt-4">
        <a href="{{ route('register') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">
            Modifier mes informations
        </a>
    </div>
</div>
@endsection
