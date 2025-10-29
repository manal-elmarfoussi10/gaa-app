<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous - GA Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }
        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img class="h-8 w-auto" src="{{ asset('images/GA GESTION LOGO.png') }}" alt="GA Gestion">
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-700 hover:text-[#FF4B00] px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Accueil
                    </a>
                    <a href="#contact" class="bg-[#FF4B00] text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-[#E64400] transition-colors">
                        Contact
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative gradient-bg text-white overflow-hidden">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-5xl md:text-6xl font-bold mb-6">
                    Parlons de votre
                    <span class="text-yellow-300">projet</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
                    Que vous ayez besoin d'une démonstration, d'informations sur nos services,
                    ou que vous souhaitiez devenir partenaire, nous sommes là pour vous accompagner.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#contact-form" class="bg-white text-[#FF4B00] px-8 py-4 rounded-full font-semibold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                        Commencer maintenant
                    </a>
                    <a href="tel:0184806832" class="border-2 border-white text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white hover:text-[#FF4B00] transition-all">
                        📞 Nous appeler
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section id="contact-form" class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

                <!-- Contact Info -->
                <div class="space-y-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">
                            Restons en contact
                        </h2>
                        <p class="text-gray-600 text-lg">
                            Notre équipe est disponible pour répondre à toutes vos questions
                            et vous accompagner dans votre projet.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="bg-[#FF4B00] p-3 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Téléphone</h3>
                                <p class="text-gray-600">01 84 80 68 32</p>
                                <p class="text-sm text-gray-500">Lundi au Samedi, 9h-18h</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-[#FF4B00] p-3 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Email</h3>
                                <p class="text-gray-600">contact@gagestion.fr</p>
                                <p class="text-sm text-gray-500">Réponse sous 24h</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-[#FF4B00] p-3 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Adresse</h3>
                                <p class="text-gray-600">Paris, France</p>
                                <p class="text-sm text-gray-500">Zone disponible sur demande</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Proof -->
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-4">Pourquoi nous choisir ?</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Support technique 24/7
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Formation personnalisée
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Mise en service rapide
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="glass-card rounded-2xl p-8 shadow-xl">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Envoyez-nous un message</h3>

                    @if(session('success'))
                        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg border border-green-200 mb-6 text-sm font-medium">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.send') }}" class="space-y-6">
                        @csrf

                        <!-- Type Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">
                                Quel est l'objet de votre demande ? *
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-[#FF4B00] hover:bg-[#FF4B00]/5 cursor-pointer transition-all">
                                    <input type="radio" name="type" value="general" class="text-[#FF4B00] focus:ring-[#FF4B00]" required>
                                    <div>
                                        <span class="font-medium text-gray-900">Contact général</span>
                                        <p class="text-sm text-gray-500">Questions, support ou informations</p>
                                    </div>
                                </label>
                                <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-[#FF4B00] hover:bg-[#FF4B00]/5 cursor-pointer transition-all">
                                    <input type="radio" name="type" value="demo" class="text-[#FF4B00] focus:ring-[#FF4B00]">
                                    <div>
                                        <span class="font-medium text-gray-900">Demande de démonstration</span>
                                        <p class="text-sm text-gray-500">Découvrez nos fonctionnalités en live</p>
                                    </div>
                                </label>
                                <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-[#FF4B00] hover:bg-[#FF4B00]/5 cursor-pointer transition-all">
                                    <input type="radio" name="type" value="partner" class="text-[#FF4B00] focus:ring-[#FF4B00]">
                                    <div>
                                        <span class="font-medium text-gray-900">Devenir partenaire</span>
                                        <p class="text-sm text-gray-500">Opportunités de collaboration commerciale</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet *</label>
                            <input type="text" name="name" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF4B00] focus:border-transparent transition-all">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Adresse email *</label>
                            <input type="email" name="email" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF4B00] focus:border-transparent transition-all">
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Votre message *</label>
                            <textarea name="message" rows="5" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF4B00] focus:border-transparent transition-all resize-none"
                                placeholder="Décrivez votre demande en détail..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-[#FF4B00] to-[#FF6B3D] text-white font-semibold py-4 rounded-lg hover:from-[#E64400] hover:to-[#FF5722] transition-all duration-300 transform hover:scale-105 shadow-lg">
                            📤 Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <img class="h-8 w-auto mb-4" src="{{ asset('images/GA GESTION LOGO WHITE.png') }}" alt="GA Gestion">
                    <p class="text-gray-400">
                        Solution de gestion d'entreprise complète et intuitive.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <p class="text-gray-400">📞 01 84 80 68 32</p>
                    <p class="text-gray-400">✉️ contact@gagestion.fr</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Horaires</h3>
                    <p class="text-gray-400">Lundi - Samedi</p>
                    <p class="text-gray-400">9h00 - 18h00</p>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 GA Gestion. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>
