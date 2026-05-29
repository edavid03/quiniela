<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name', 'Quiniela').' · Quinielas privadas para tu grupo')</title>
        @php
            $ogTitle = config('app.name', 'Quiniela Mundial').' · Tu quiniela privada del Mundial 2026';
            $ogDescription = 'Arma tu quiniela privada del Mundial 2026: ligas con su propio admin, pronósticos, ranking automático y resultados centralizados.';
            $ogImage = asset('images/og-image.png');
        @endphp
        <meta name="description" content="{{ $ogDescription }}">

        {{-- Open Graph / Twitter (previews al compartir el link) --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name', 'Quiniela Mundial') }}">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:secure_url" content="{{ $ogImage }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:locale" content="es_LA">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDescription }}">
        <meta name="twitter:image" content="{{ $ogImage }}">

        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('images/favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=archivo:400,500,600,700,800,900|sora:600,700,800" rel="stylesheet" />
        <script>
            (() => {
                const storedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = storedTheme || (prefersDark ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.dataset.theme = theme;
            })();
        </script>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen antialiased">
        <header class="sticky top-0 z-30 border-b border-[var(--app-border)] bg-[var(--app-panel)]/88 backdrop-blur-xl">
            <div class="app-shell flex items-center justify-between gap-3 py-2.5 md:py-3">
                <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2 no-underline md:gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-1 md:h-12 md:w-12">
                        <img src="{{ asset('images/fifa-world-cup-2026.svg') }}" alt="{{ config('app.name', 'Quiniela') }}" class="h-full w-full object-contain dark:brightness-0 dark:invert">
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate font-display text-sm font-black leading-tight text-[var(--app-text)] md:text-lg">{{ config('app.name', 'Quiniela Mundial') }}</span>
                        <span class="hidden text-xs font-extrabold uppercase text-[var(--app-muted)] md:block">#SOMOS26</span>
                    </span>
                </a>

                <nav class="flex items-center gap-2 text-sm">
                    <a href="#como-funciona" class="btn btn-secondary hidden sm:inline-flex">Cómo funciona</a>
                    <button type="button" data-theme-toggle aria-label="Cambiar tema" title="Cambiar tema" class="btn btn-secondary w-11 px-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 dark:hidden" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="hidden h-5 w-5 dark:block" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </button>
                    <a href="#contacto" class="btn btn-primary">Contáctanos</a>
                </nav>
            </div>
        </header>

        <main class="page-fade">
            @yield('content')
        </main>

        @php
            $igRaw = config('contact.instagram');
            $xRaw = config('contact.x');
            $waRaw = config('contact.whatsapp');
            $igUrl = $igRaw ? (str_starts_with($igRaw, 'http') ? $igRaw : 'https://instagram.com/'.ltrim($igRaw, '@')) : null;
            $xUrl = $xRaw ? (str_starts_with($xRaw, 'http') ? $xRaw : 'https://x.com/'.ltrim($xRaw, '@')) : null;
            $waUrl = $waRaw ? 'https://wa.me/'.preg_replace('/\D+/', '', $waRaw) : null;
            $socialClass = 'grid h-9 w-9 place-items-center rounded-lg border border-current/15 bg-current/10 opacity-80 no-underline transition hover:bg-current/20 hover:opacity-100';
            $footerLink = 'text-sm font-bold no-underline opacity-75 transition hover:opacity-100';
            $footerHead = 'font-display text-xs font-black uppercase tracking-wide opacity-55';
        @endphp
        <footer class="site-footer">
            <div class="app-shell py-12 md:py-14">
                <div class="grid gap-10 md:grid-cols-[1.5fr_1fr_1fr_1.2fr]">
                    {{-- Marca --}}
                    <div>
                        <a href="{{ url('/') }}" class="flex items-center gap-3 no-underline">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-current/10 p-1.5">
                                <img src="{{ asset('images/fifa-world-cup-2026.svg') }}" alt="" class="h-full w-full object-contain dark:brightness-0 dark:invert">
                            </span>
                            <span class="font-display text-base font-black">{{ config('app.name', 'Quiniela Mundial') }}</span>
                        </a>
                        <p class="mt-4 max-w-xs text-sm font-semibold leading-6 opacity-70">Arma la quiniela privada de tu grupo para el Mundial 2026: pronósticos, ranking automático y resultados centralizados.</p>

                        <div class="mt-5 flex items-center gap-2">
                            @if ($igUrl)
                                <a href="{{ $igUrl }}" target="_blank" rel="noopener" aria-label="Instagram" class="{{ $socialClass }}"><x-social-icon name="instagram" class="h-[1.1rem] w-[1.1rem]" /></a>
                            @endif
                            @if ($xUrl)
                                <a href="{{ $xUrl }}" target="_blank" rel="noopener" aria-label="X" class="{{ $socialClass }}"><x-social-icon name="x" class="h-4 w-4" /></a>
                            @endif
                            @if ($waUrl)
                                <a href="{{ $waUrl }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="{{ $socialClass }}"><x-social-icon name="whatsapp" class="h-[1.1rem] w-[1.1rem]" /></a>
                            @endif
                            <a href="mailto:{{ config('contact.email') }}" aria-label="Email" class="{{ $socialClass }}"><x-icon name="envelope" class="h-[1.1rem] w-[1.1rem]" /></a>
                        </div>
                    </div>

                    {{-- Producto --}}
                    <nav class="grid content-start gap-3">
                        <span class="{{ $footerHead }}">Producto</span>
                        <a href="#que-ofrecemos" class="{{ $footerLink }}">Qué ofrecemos</a>
                        <a href="#como-funciona" class="{{ $footerLink }}">Cómo funciona</a>
                        <a href="#contacto" class="{{ $footerLink }}">Contacto</a>
                    </nav>

                    {{-- Plataforma --}}
                    <nav class="grid content-start gap-3">
                        <span class="{{ $footerHead }}">Plataforma</span>
                        <a href="{{ route('superadmin.login') }}" class="{{ $footerLink }}">Acceso administradores</a>
                        <a href="#contacto" class="{{ $footerLink }}">Quiero mi liga</a>
                    </nav>

                    {{-- Contacto --}}
                    <div class="grid content-start gap-3">
                        <span class="{{ $footerHead }}">Contacto</span>
                        <a href="mailto:{{ config('contact.email') }}" class="{{ $footerLink }}">{{ config('contact.email') }}</a>
                        <span class="text-sm font-semibold leading-6 opacity-60">Te respondemos dentro de las 24 horas.</span>
                    </div>
                </div>

                <div class="mt-10 flex flex-col gap-2 border-t border-current/10 pt-6 text-xs font-semibold opacity-60 sm:flex-row sm:items-center sm:justify-between">
                    <span>© {{ now()->year }} {{ config('app.name', 'Quiniela Mundial') }} · Quinielas privadas del Mundial 2026.</span>
                    <span class="font-extrabold uppercase tracking-wide opacity-70">Hecho para el fútbol · #SOMOS26</span>
                </div>
            </div>
        </footer>
    </body>
</html>
