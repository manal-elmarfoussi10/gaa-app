@extends('layouts.guest')

@section('title', 'Inscription')

@section('content')
<div class="relative min-h-screen overflow-hidden">
    {{-- Animated Background --}}
    <div class="absolute inset-0 bg-gradient-to-br from-orange-50 via-white to-orange-100">
        <div class="absolute inset-0 bg-gradient-to-r from-orange-400/10 via-transparent to-orange-600/10 animate-pulse"></div>
        <div class="absolute top-0 left-0 w-full h-full">
            <div class="absolute top-20 left-10 w-32 h-32 bg-orange-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute top-40 right-10 w-32 h-32 bg-orange-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-20 left-20 w-32 h-32 bg-orange-400 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
            <div class="absolute bottom-40 right-20 w-32 h-32 bg-orange-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-6000"></div>
        </div>
    </div>

    {{-- Content --}}
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-2xl">
            {{-- Logo --}}
            <div class="flex justify-center mb-8">
                <img src="{{ asset('images/GS.png') }}" alt="GS Auto" class="h-20 w-auto drop-shadow-lg transition-transform hover:scale-105">
            </div>

            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-8 md:p-12">
                <h2 class="text-4xl font-bold text-center text-gray-900 mb-3">Création de votre compte</h2>
                <p class="text-gray-600 text-center mb-8 text-lg">Rejoignez GS Auto et créez votre compte professionnel.</p>

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

                <form method="POST" action="{{ route('register') }}" class="space-y-6" novalidate>
                    @csrf

                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- First Name --}}
                        <div class="relative group">
                            <label for="first_name" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Prénom *</label>
                            <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('first_name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Last Name --}}
                        <div class="relative group">
                            <label for="last_name" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Nom *</label>
                            <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('last_name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="relative group">
                        <label for="email" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Adresse Email *</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder=" "
                               class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                        @error('email') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Company Name --}}
                        <div class="relative group">
                            <label for="company_name" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Nom de la société *</label>
                            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('company_name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Commercial Name --}}
                        <div class="relative group">
                            <label for="commercial_name" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Nom commercial</label>
                            <input id="commercial_name" type="text" name="commercial_name" value="{{ old('commercial_name') }}" placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('commercial_name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        {{-- Phone --}}
                        <div class="relative group">
                            <label for="phone" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Téléphone *</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('phone') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Siret --}}
                        <div class="relative group">
                            <label for="siret" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Numéro de siret *</label>
                            <input id="siret" type="text" name="siret" value="{{ old('siret') }}" required placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('siret') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- TVA --}}
                        <div class="relative group">
                            <label for="tva" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Numéro de TVA *</label>
                            <input id="tva" type="text" name="tva" value="{{ old('tva') }}" required placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('tva') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Garage Type --}}
                    <div>
                        <span class="block text-base font-semibold text-gray-800 mb-4">Quel type de garage avez-vous ?</span>
                        <div class="grid gap-4 md:grid-cols-3">
                            <label class="flex items-center p-5 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm cursor-pointer hover:border-orange-400 hover:bg-white hover:shadow-xl transition-all duration-200 shadow-lg">
                                <input type="radio" name="garage_type" value="fixe" {{ old('garage_type') == 'fixe' ? 'checked' : '' }} class="mr-4 w-5 h-5 text-orange-600 focus:ring-orange-500">
                                <div>
                                    <div class="font-semibold text-gray-900">Fixe</div>
                                    <div class="text-sm text-gray-600">Changement pare brise sur place</div>
                                </div>
                            </label>
                            <label class="flex items-center p-5 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm cursor-pointer hover:border-orange-400 hover:bg-white hover:shadow-xl transition-all duration-200 shadow-lg">
                                <input type="radio" name="garage_type" value="mobile" {{ old('garage_type') == 'mobile' ? 'checked' : '' }} class="mr-4 w-5 h-5 text-orange-600 focus:ring-orange-500">
                                <div>
                                    <div class="font-semibold text-gray-900">Mobile</div>
                                    <div class="text-sm text-gray-600">Changement chez le client</div>
                                </div>
                            </label>
                            <label class="flex items-center p-5 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm cursor-pointer hover:border-orange-400 hover:bg-white hover:shadow-xl transition-all duration-200 shadow-lg">
                                <input type="radio" name="garage_type" value="both" {{ old('garage_type') == 'both' ? 'checked' : '' }} class="mr-4 w-5 h-5 text-orange-600 focus:ring-orange-500">
                                <div>
                                    <div class="font-semibold text-gray-900">Les deux</div>
                                    <div class="text-sm text-gray-600">Service complet</div>
                                </div>
                            </label>
                        </div>
                        @error('garage_type') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Known By --}}
                    <div>
                        <label for="known_by" class="block text-base font-semibold text-gray-800 mb-4">Comment avez-vous connu GS Auto ?</label>
                        <select id="known_by" name="known_by" class="w-full rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm px-4 py-4 focus:border-orange-400 focus:bg-white transition-all duration-200 shadow-lg hover:shadow-xl appearance-none">
                            <option value="">Sélectionnez une option</option>
                            <option value="site_web" {{ old('known_by') == 'site_web' ? 'selected' : '' }}>Site web</option>
                            <option value="google" {{ old('known_by') == 'google' ? 'selected' : '' }}>Google</option>
                            <option value="parrainage" {{ old('known_by') == 'parrainage' ? 'selected' : '' }}>Parrainage / Recommandation</option>
                            <option value="evenements" {{ old('known_by') == 'evenements' ? 'selected' : '' }}>Évènements</option>
                            <option value="prospection" {{ old('known_by') == 'prospection' ? 'selected' : '' }}>Prospection</option>
                            <option value="chatgpt" {{ old('known_by') == 'chatgpt' ? 'selected' : '' }}>ChatGPT</option>
                            <option value="reseaux_sociaux" {{ old('reseaux_sociaux') == 'reseaux_sociaux' ? 'selected' : '' }}>Réseaux sociaux</option>
                            <option value="emailing" {{ old('known_by') == 'emailing' ? 'selected' : '' }}>E-mailing</option>
                            <option value="autre" {{ old('known_by') == 'autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('known_by') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Password --}}
                        <div class="relative group">
                            <label for="password" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Mot de passe *</label>
                            <input id="password" type="password" name="password" required placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('password') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password Confirmation --}}
                        <div class="relative group">
                            <label for="password_confirmation" class="absolute left-4 top-4 text-sm font-medium text-gray-500 transition-all duration-200 group-focus-within:-translate-y-2 group-focus-within:scale-75 group-focus-within:text-orange-600 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100">Répéter le mot de passe *</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder=" "
                                   class="w-full pt-6 pb-3 px-4 rounded-2xl border-2 border-gray-200 bg-white/50 backdrop-blur-sm focus:border-orange-400 focus:bg-white transition-all duration-200 peer shadow-lg hover:shadow-xl">
                            @error('password_confirmation') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Terms --}}
                    <div class="flex items-start bg-white/30 backdrop-blur-sm rounded-2xl p-6 border-2 border-gray-200">
                        <input type="checkbox" name="terms" value="1" required class="mt-1 mr-4 w-5 h-5 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500">
                        <label class="text-sm text-gray-700 cursor-pointer leading-relaxed">
                            J'accepte les <a href="https://gservicesauto.com/politique-confidentialite/" target="_blank" class="text-orange-600 hover:text-orange-700 underline font-semibold">conditions générales d'utilisation</a> et la <a href="https://gservicesauto.com/mentions-legales/" target="_blank" class="text-orange-600 hover:text-orange-700 underline font-semibold">politique de confidentialité</a>
                        </label>
                    </div>
                    @error('terms') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror

                    {{-- Submit --}}
                    <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 py-4 text-xl font-bold text-white transition-all duration-200 hover:from-orange-600 hover:to-orange-700 hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98] shadow-xl">
                        Créer mon compte
                    </button>
                </form>

                {{-- Login link --}}
                <div class="text-center mt-8">
                    <span class="text-gray-600">Déjà un compte ?</span>
                    <a href="{{ route('login') }}" class="text-orange-600 hover:text-orange-700 transition-colors ml-2 font-semibold">Se connecter</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom CSS for animations --}}
<style>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

.animation-delay-6000 {
  animation-delay: 6s;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

::-webkit-scrollbar-thumb {
  background: rgba(255, 107, 0, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 107, 0, 0.5);
}
</style>
@endsection
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
