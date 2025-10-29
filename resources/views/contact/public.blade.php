@extends('layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-[#FF4B00] via-[#FF6B3D] to-[#FF8A6B] flex items-center justify-center px-4 py-16">
    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden">

        <!-- Header Section -->
        <div class="bg-gradient-to-r from-[#FF4B00] to-[#FF6B3D] text-white text-center py-12 px-6">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight">
                Contactez-nous
            </h1>
            <p class="text-xl md:text-2xl opacity-90">
                Nous sommes là pour vous aider
            </p>
        </div>

        <div class="flex flex-col lg:flex-row">

            <!-- Contact Info Section -->
            <div class="lg:w-1/2 bg-gray-50 p-8 md:p-12 space-y-8">
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        Informations de contact
                    </h2>
                    <div class="space-y-6">
                        <div class="flex items-center justify-center lg:justify-start space-x-4">
                            <div class="bg-[#FF4B00] p-3 rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-800">Téléphone</p>
                                <p class="text-gray-600">01 84 80 68 32</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-center lg:justify-start space-x-4">
                            <div class="bg-[#FF4B00] p-3 rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-800">Email</p>
                                <p class="text-gray-600">contact@gagestion.fr</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-center lg:justify-start space-x-4">
                            <div class="bg-[#FF4B00] p-3 rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-800">Horaires</p>
                                <p class="text-gray-600">Lundi au Samedi<br>9h à 18h</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <div class="lg:w-1/2 p-8 md:p-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center lg:text-left">
                    Envoyez-nous un message
                </h2>

                @if(session('success'))
                    <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg border border-green-200 mb-6 text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.send') }}" class="space-y-6">
                    @csrf

                    <!-- Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Type de demande *
                        </label>
                        <div class="grid grid-cols-1 gap-3">
                            <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-[#FF4B00] cursor-pointer transition-colors">
                                <input type="radio" name="type" value="general" class="text-[#FF4B00] focus:ring-[#FF4B00]" required>
                                <div>
                                    <span class="font-medium text-gray-900">Contact général</span>
                                    <p class="text-sm text-gray-500">Questions générales ou support</p>
                                </div>
                            </label>
                            <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-[#FF4B00] cursor-pointer transition-colors">
                                <input type="radio" name="type" value="demo" class="text-[#FF4B00] focus:ring-[#FF4B00]">
                                <div>
                                    <span class="font-medium text-gray-900">Demande de démonstration</span>
                                    <p class="text-sm text-gray-500">Découvrez nos fonctionnalités</p>
                                </div>
                            </label>
                            <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-[#FF4B00] cursor-pointer transition-colors">
                                <input type="radio" name="type" value="partner" class="text-[#FF4B00] focus:ring-[#FF4B00]">
                                <div>
                                    <span class="font-medium text-gray-900">Devenir partenaire</span>
                                    <p class="text-sm text-gray-500">Opportunités de collaboration</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet *</label>
                        <input type="text" name="name" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#FF4B00] focus:border-transparent transition-all">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse email *</label>
                        <input type="email" name="email" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#FF4B00] focus:border-transparent transition-all">
                    </div>

                    <!-- Message -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                        <textarea name="message" rows="5" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#FF4B00] focus:border-transparent transition-all resize-none"
                            placeholder="Décrivez votre demande..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-[#FF4B00] to-[#FF6B3D] text-white font-semibold py-4 rounded-xl hover:from-[#E64400] hover:to-[#FF5722] transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
