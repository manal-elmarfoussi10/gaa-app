@extends('layouts.guest')

@section('title', 'Inscription')

@section('content')
<div class="fade-in">
    {{-- Logo --}}
    <div class="flex justify-center mb-8">
        <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-24 w-auto drop-shadow-lg transition-transform hover:scale-105">
    </div>

    <h2 class="text-3xl font-bold text-center text-gray-900 mb-2">Création de votre compte</h2>
    <p class="text-gray-600 text-center mb-8">Rejoignez GS Auto et créez votre compte professionnel.</p>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 animate-pulse">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 animate-pulse">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf

        {{-- First Name --}}
        <div class="relative">
            <label for="first_name" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Prénom *</label>
            <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('first_name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Last Name --}}
        <div class="relative">
            <label for="last_name" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Nom *</label>
            <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('last_name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Email --}}
        <div class="relative">
            <label for="email" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Adresse Email *</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('email') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Company Name --}}
        <div class="relative">
            <label for="company_name" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Nom de la société *</label>
            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('company_name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Commercial Name --}}
        <div class="relative">
            <label for="commercial_name" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Nom commercial</label>
            <input id="commercial_name" type="text" name="commercial_name" value="{{ old('commercial_name') }}" placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('commercial_name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Phone --}}
        <div class="relative">
            <label for="phone" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Numéro de téléphone *</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('phone') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Siret --}}
        <div class="relative">
            <label for="siret" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Numéro de siret *</label>
            <input id="siret" type="text" name="siret" value="{{ old('siret') }}" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('siret') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- TVA --}}
        <div class="relative">
            <label for="tva" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Numéro de TVA *</label>
            <input id="tva" type="text" name="tva" value="{{ old('tva') }}" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('tva') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Garage Type --}}
        <div>
            <span class="block text-sm font-medium text-gray-700 mb-3">Quel type de garage avez-vous ?</span>
            <div class="grid gap-3">
                <label class="flex items-center p-4 rounded-xl border-2 border-gray-200 bg-white cursor-pointer hover:border-orange-400 transition-all duration-200">
                    <input type="radio" name="garage_type" value="fixe" {{ old('garage_type') == 'fixe' ? 'checked' : '' }} class="mr-3 w-4 h-4 text-orange-600 focus:ring-orange-500">
                    <span class="text-gray-700">Fixe - Changement pare brise sur place</span>
                </label>
                <label class="flex items-center p-4 rounded-xl border-2 border-gray-200 bg-white cursor-pointer hover:border-orange-400 transition-all duration-200">
                    <input type="radio" name="garage_type" value="mobile" {{ old('garage_type') == 'mobile' ? 'checked' : '' }} class="mr-3 w-4 h-4 text-orange-600 focus:ring-orange-500">
                    <span class="text-gray-700">Mobile - Changement chez le client</span>
                </label>
                <label class="flex items-center p-4 rounded-xl border-2 border-gray-200 bg-white cursor-pointer hover:border-orange-400 transition-all duration-200">
                    <input type="radio" name="garage_type" value="both" {{ old('garage_type') == 'both' ? 'checked' : '' }} class="mr-3 w-4 h-4 text-orange-600 focus:ring-orange-500">
                    <span class="text-gray-700">Les deux</span>
                </label>
            </div>
            @error('garage_type') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Known By --}}
        <div>
            <label for="known_by" class="block text-base font-semibold text-gray-800 mb-3">Comment avez-vous connu GS Auto ?</label>
            <select id="known_by" name="known_by" class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 appearance-none">
                <option value="">Sélectionnez une option</option>
                <option value="site_web" {{ old('known_by') == 'site_web' ? 'selected' : '' }}>Site web</option>
                <option value="google" {{ old('known_by') == 'google' ? 'selected' : '' }}>Google</option>
                <option value="parrainage" {{ old('known_by') == 'parrainage' ? 'selected' : '' }}>Parrainage / Recommandation</option>
                <option value="evenements" {{ old('known_by') == 'evenements' ? 'selected' : '' }}>Évènements</option>
                <option value="prospection" {{ old('known_by') == 'prospection' ? 'selected' : '' }}>Prospection</option>
                <option value="chatgpt" {{ old('known_by') == 'chatgpt' ? 'selected' : '' }}>ChatGPT</option>
                <option value="reseaux_sociaux" {{ old('known_by') == 'reseaux_sociaux' ? 'selected' : '' }}>Réseaux sociaux</option>
                <option value="emailing" {{ old('known_by') == 'emailing' ? 'selected' : '' }}>E-mailing</option>
                <option value="autre" {{ old('known_by') == 'autre' ? 'selected' : '' }}>Autre</option>
            </select>
            @error('known_by') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Password --}}
        <div class="relative">
            <label for="password" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Mot de passe *</label>
            <input id="password" type="password" name="password" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('password') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Password Confirmation --}}
        <div class="relative">
            <label for="password_confirmation" class="absolute left-3 top-3 text-sm font-medium text-gray-500 transition-all duration-200 transform -translate-y-1 scale-75 origin-top-left peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-1 peer-focus:scale-75 peer-focus:text-orange-600">Répéter le mot de passe *</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder=" " class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 focus:border-orange-400 transition-all duration-200 peer">
            @error('password_confirmation') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Terms --}}
        <div class="flex items-start">
            <input type="checkbox" name="terms" value="1" required class="mt-1 mr-3 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500">
            <label class="text-sm text-gray-600 cursor-pointer">
                J'accepte les <a href="https://gservicesauto.com/politique-confidentialite/" target="_blank" class="text-orange-600 hover:text-orange-700 underline">conditions générales d'utilisation</a> et la <a href="https://gservicesauto.com/mentions-legales/" target="_blank" class="text-orange-600 hover:text-orange-700 underline">politique de confidentialité</a>
            </label>
        </div>
        @error('terms') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror

        {{-- Submit --}}
        <button type="submit" class="w-full rounded-xl bg-black py-3 text-lg font-bold text-white transition-all duration-200 hover:bg-gray-900 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
            Créer mon compte
        </button>
    </form>

    {{-- Login link --}}
    <div class="text-center mt-6">
        <span class="text-sm text-gray-600">Déjà un compte ?</span>
        <a href="{{ route('login') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors ml-1">Se connecter</a>
    </div>
</div>
@endsection
