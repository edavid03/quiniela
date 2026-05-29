<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name', 'Quiniela'))</title>
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
    <body data-auth="{{ auth()->check() ? 'auth' : 'guest' }}" class="min-h-screen antialiased">
        @auth
            <header class="sticky top-0 z-30 border-b border-[var(--app-border)] bg-[var(--app-panel)]/88 backdrop-blur-xl">
                <div class="app-shell flex items-center justify-between gap-2 py-1.5 md:flex-wrap md:gap-4 md:py-3">
                    <a href="{{ route('liga.dashboard', ['liga' => $currentLiga]) }}" class="flex min-w-0 items-center gap-2 no-underline md:gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-1 shadow-[0_4px_0_color-mix(in_srgb,var(--app-primary-strong)_70%,#000)] md:h-14 md:w-14 md:p-1.5 md:shadow-[0_8px_0_color-mix(in_srgb,var(--app-primary-strong)_70%,#000)]">
                            <img src="{{ asset('images/fifa-world-cup-2026.svg') }}" alt="FIFA World Cup 2026" class="h-full w-full object-contain dark:brightness-0 dark:invert" loading="eager" decoding="async">
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate font-display text-sm font-black leading-tight text-[var(--app-text)] md:text-lg">Quiniela Mundial</span>
                            <span class="hidden text-xs font-extrabold uppercase text-[var(--app-muted)] md:block">#SOMOS26</span>
                        </span>
                    </a>

                    <nav class="hidden flex-wrap items-center gap-2 text-sm md:flex">
                        <a href="{{ route('liga.dashboard', ['liga' => $currentLiga]) }}" class="btn btn-secondary {{ request()->routeIs('liga.dashboard') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Mesa</a>
                        <a href="{{ route('liga.pronosticos.edit', ['liga' => $currentLiga]) }}" class="btn btn-secondary {{ request()->routeIs('liga.pronosticos.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Pronosticos</a>
                        <a href="{{ route('liga.resultados.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary {{ request()->routeIs('liga.resultados.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Resultados</a>
                        <a href="{{ route('liga.rankings.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary {{ request()->routeIs('liga.rankings.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Ranking</a>
                        @if (auth()->user()->isLigaAdmin())
                            <a href="{{ route('liga.admin.users.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary {{ request()->routeIs('liga.admin.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Admin</a>
                        @endif
                    </nav>

                    <div class="flex min-w-0 items-center gap-1.5 text-xs md:flex-wrap md:gap-2 md:text-sm">
                        <span class="flex min-h-9 max-w-32 items-center gap-2 rounded-full bg-[var(--app-panel-soft)] px-1.5 py-1 font-bold text-[var(--app-text)] sm:max-w-40 md:min-h-11 md:max-w-none md:px-2.5">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[var(--app-secondary)] font-display text-[11px] font-black text-white md:h-7 md:w-7 md:text-xs">{{ strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)) }}</span>
                            <span class="truncate">{{ auth()->user()->name }}</span>
                        </span>
                        <button type="button" data-theme-toggle aria-label="Cambiar tema" title="Cambiar tema" class="btn btn-secondary hidden w-11 px-0 md:inline-flex">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 dark:hidden" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="hidden h-5 w-5 dark:block" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('liga.logout', ['liga' => $currentLiga]) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary min-h-0 px-2.5 py-1.5 text-xs md:min-h-11 md:px-4 md:py-2.5 md:text-sm">Salir</button>
                        </form>
                    </div>
                </div>
            </header>

            <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-[var(--app-border)] bg-[var(--app-panel)]/94 px-2 pb-[calc(.75rem+env(safe-area-inset-bottom))] pt-2 shadow-[0_-18px_50px_rgba(18,11,36,.14)] backdrop-blur-xl md:hidden" aria-label="Navegacion principal">
                <div class="mx-auto grid max-w-md {{ auth()->user()->isLigaAdmin() ? 'grid-cols-5' : 'grid-cols-4' }} gap-1.5">
                    <a href="{{ route('liga.dashboard', ['liga' => $currentLiga]) }}" class="btn btn-secondary min-h-12 px-2 text-xs {{ request()->routeIs('liga.dashboard') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)] text-[var(--app-primary)]' : '' }}">Mesa</a>
                    <a href="{{ route('liga.pronosticos.edit', ['liga' => $currentLiga]) }}" class="btn btn-secondary min-h-12 px-2 text-xs {{ request()->routeIs('liga.pronosticos.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)] text-[var(--app-primary)]' : '' }}">Pronosticos</a>
                    <a href="{{ route('liga.resultados.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary min-h-12 px-1 text-[11px] {{ request()->routeIs('liga.resultados.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)] text-[var(--app-primary)]' : '' }}">Resultados</a>
                    <a href="{{ route('liga.rankings.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary min-h-12 px-2 text-xs {{ request()->routeIs('liga.rankings.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)] text-[var(--app-primary)]' : '' }}">Ranking</a>
                    @if (auth()->user()->isLigaAdmin())
                        <a href="{{ route('liga.admin.users.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary min-h-12 px-1 text-xs {{ request()->routeIs('liga.admin.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)] text-[var(--app-primary)]' : '' }}">Admin</a>
                    @endif
                </div>
            </nav>
        @endauth

        <main class="page-fade @auth app-shell pb-28 pt-5 md:py-8 @else grid min-h-screen place-items-center px-3 py-5 sm:px-4 sm:py-10 @endauth">
            @yield('content')
        </main>
    </body>
</html>
