<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@latest"></script>

        <title>{{ config('app.name', 'GS AUTO') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles (manually included) -->
        <link rel="stylesheet" href="{{ asset('build/assets/app-BqEI6us2.css') }}">

        <!-- Scripts (manually included) -->
        <script type="module" src="{{ asset('build/assets/app-DaBYqt0m.js') }}"></script>

        <style>
            .fade-in {
                animation: fadeIn 0.8s ease-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .orange-accent {
                color: #FF4B00;
            }
            .orange-bg {
                background-color: #FF4B00;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-gray-50 to-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <div class="w-full sm:max-w-xl mt-6 px-8 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-2xl fade-in border border-gray-100">
                @yield('content')
            </div>
        </div>
    </body>
</html>
