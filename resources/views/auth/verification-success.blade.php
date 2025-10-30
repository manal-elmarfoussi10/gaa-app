@extends('layouts.guest')

@section('title', 'Inscription Réussie')

@section('content')
<div class="fade-in text-center">
    {{-- Logo --}}
    <div class="flex justify-center mb-8">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-24 w-auto drop-shadow-lg transition-transform hover:scale-105">
    </div>

    {{-- Success Icon --}}
    <div class="flex justify-center mb-6">
        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
    </div>

    <h2 class="text-3xl font-bold text-gray-900 mb-4">Inscription réussie !</h2>
    <p class="text-gray-600 text-lg mb-8 max-w-md mx-auto">
        Votre compte a été créé avec succès. Notre équipe va examiner votre demande et vous contactera dans les plus brefs délais.
    </p>

    {{-- Info Cards --}}
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 mb-8 max-w-lg mx-auto">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="font-semibold text-gray-900">Délai de traitement</div>
                    <div class="text-sm text-gray-600">24-48 heures ouvrées</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="font-semibold text-gray-900">Contact</div>
                    <div class="text-sm text-gray-600">Par email ou téléphone</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="font-semibold text-gray-900">Validation finale</div>
                    <div class="text-sm text-gray-600">Activation de votre compte</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-4">
        <a href="{{ route('login') }}" class="inline-block w-full max-w-xs rounded-xl bg-black py-3 text-lg font-bold text-white transition-all duration-200 hover:bg-gray-900 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
            Aller à la connexion
        </a>

        <div class="text-center">
            <a href="{{ route('contact.public') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors">
                Contactez-nous directement
            </a>
        </div>
    </div>
</div>
@endsection
