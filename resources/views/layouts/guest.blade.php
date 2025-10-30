<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name', 'GS Auto'))</title>

  {{-- Tailwind (CDN for speed; remove if you compile locally) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { 500: '#FF4B00', 600: '#FF6A1A', 700: '#E24500' }
          },
          boxShadow: {
            card: '0 8px 30px rgba(0,0,0,.08)',
            soft: '0 4px 16px rgba(0,0,0,.06)'
          },
          animation: {
            'float-slow': 'float 9s ease-in-out infinite',
            'fade-up': 'fadeUp .6s ease-out both',
          },
          keyframes: {
            float: {
              '0%,100%': { transform: 'translateY(0)' },
              '50%': { transform: 'translateY(-10px)' }
            },
            fadeUp: {
              '0%': { opacity: 0, transform: 'translateY(10px)' },
              '100%': { opacity: 1, transform: 'translateY(0)' }
            }
          }
        }
      }
    }
  </script>

  {{-- Optional icon fonts (tiny) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

  <style>
    html, body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; }
    /* Subtle gradient background with animated “glows” */
    .bg-hero {
      background: radial-gradient(1200px 600px at 10% -10%, rgba(255,120,48,.25), transparent 60%),
                  radial-gradient(1000px 500px at 110% 10%, rgba(255,180,120,.2), transparent 60%),
                  linear-gradient(to bottom right, #fff, #fff 40%, #fff 70%, #fff);
    }
    .glow {
      filter: blur(60px);
      opacity: .35;
    }
  </style>
  @stack('head')
</head>
<body class="min-h-dvh bg-hero text-gray-900 antialiased">

  {{-- Top navigation ribbon (simple) --}}
  <header class="sticky top-0 z-40 backdrop-blur bg-white/70 border-b border-white/60">
    <div class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between">
      <a href="{{ url('/') }}" class="group flex items-center gap-3">
        <img src="/images/GS.png" alt="GS Auto" class="h-8 w-auto transition-transform group-hover:scale-105">
        <span class="hidden sm:block font-extrabold tracking-tight">GS Auto</span>
      </a>
      <div class="flex items-center gap-2">
        @hasSection('headerActions')
          @yield('headerActions')
        @else
          <a href="{{ route('login') }}"
             class="hidden sm:inline-flex items-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-gray-50">
            Se connecter
          </a>
        @endif
      </div>
    </div>
  </header>

  {{-- Animated background “glows” (very soft) --}}
  <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute left-10 top-24 h-40 w-40 rounded-full bg-brand-500/20 glow animate-float-slow"></div>
    <div class="absolute right-10 top-36 h-48 w-48 rounded-full bg-brand-600/20 glow animate-float-slow" style="animation-delay:1.5s"></div>
    <div class="absolute left-1/3 bottom-20 h-56 w-56 rounded-full bg-orange-300/20 glow animate-float-slow" style="animation-delay:3s"></div>
  </div>

  {{-- Page content container (no cramped max-w) --}}
  <main class="mx-auto max-w-6xl px-4 py-10 md:py-14 animate-fade-up">
    @yield('content')
  </main>

  {{-- Minimal footer --}}
  <footer class="mt-8 border-t border-gray-100/80">
    <div class="mx-auto max-w-6xl px-4 py-6 text-xs text-gray-500 flex flex-wrap items-center justify-between gap-2">
      <div>© {{ date('Y') }} GS Auto — Tous droits réservés.</div>
      <nav class="flex items-center gap-4">
        <a class="hover:text-gray-700" href="/mentions-legales">Mentions légales</a>
        <a class="hover:text-gray-700" href="/politique-confidentialite">Confidentialité</a>
      </nav>
    </div>
  </footer>

  @stack('scripts')
</body>
</html>