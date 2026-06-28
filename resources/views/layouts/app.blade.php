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
                        @unless (auth()->user()->isLigaAdmin())
                            <a href="{{ route('liga.mi-desempeno', ['liga' => $currentLiga]) }}" class="btn btn-secondary {{ request()->routeIs('liga.mi-desempeno') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Mi desempeño</a>
                        @endunless
                        <a href="{{ route('liga.reglas.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary {{ request()->routeIs('liga.reglas.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Reglas</a>
                        @if (auth()->user()->isLigaAdmin())
                            <a href="{{ route('liga.admin.users.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary {{ request()->routeIs('liga.admin.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Admin</a>
                        @endif
                    </nav>

                    <div class="flex min-w-0 items-center gap-1.5 text-xs md:flex-wrap md:gap-2 md:text-sm">
                        <a href="{{ route('liga.profile.edit', ['liga' => $currentLiga]) }}" title="Mi cuenta" class="flex min-h-9 max-w-32 items-center gap-2 rounded-full bg-[var(--app-panel-soft)] px-1.5 py-1 font-bold text-[var(--app-text)] no-underline transition hover:bg-[var(--app-panel-strong)] sm:max-w-40 md:min-h-11 md:max-w-none md:px-2.5 {{ request()->routeIs('liga.profile.*') ? 'ring-2 ring-[var(--app-primary)]' : '' }}">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[var(--app-secondary)] font-display text-[11px] font-black text-white md:h-7 md:w-7 md:text-xs">{{ strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)) }}</span>
                            <span class="truncate">{{ auth()->user()->name }}</span>
                        </a>
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

            <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-[var(--app-border)] bg-[var(--app-panel)]/95 px-1.5 pb-[calc(.4rem+env(safe-area-inset-bottom))] pt-1.5 shadow-[0_-14px_40px_rgba(18,11,36,.16)] backdrop-blur-xl md:hidden" aria-label="Navegacion principal">
                <div class="mx-auto grid max-w-lg grid-cols-6">
                    <x-nav-tab :href="route('liga.dashboard', ['liga' => $currentLiga])" label="Mesa" :active="request()->routeIs('liga.dashboard')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    </x-nav-tab>
                    <x-nav-tab :href="route('liga.pronosticos.edit', ['liga' => $currentLiga])" label="Pronos" :active="request()->routeIs('liga.pronosticos.*')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                    </x-nav-tab>
                    <x-nav-tab :href="route('liga.resultados.index', ['liga' => $currentLiga])" label="Result" :active="request()->routeIs('liga.resultados.*')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                    </x-nav-tab>
                    <x-nav-tab :href="route('liga.rankings.index', ['liga' => $currentLiga])" label="Ranking" :active="request()->routeIs('liga.rankings.*')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" /></svg>
                    </x-nav-tab>
                    @unless (auth()->user()->isLigaAdmin())
                        <x-nav-tab :href="route('liga.mi-desempeno', ['liga' => $currentLiga])" label="Mi rend." :active="request()->routeIs('liga.mi-desempeno')">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                        </x-nav-tab>
                    @endunless
                    <x-nav-tab :href="route('liga.reglas.index', ['liga' => $currentLiga])" label="Reglas" :active="request()->routeIs('liga.reglas.*')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                    </x-nav-tab>
                    @if (auth()->user()->isLigaAdmin())
                        <x-nav-tab :href="route('liga.admin.users.index', ['liga' => $currentLiga])" label="Admin" :active="request()->routeIs('liga.admin.*')">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        </x-nav-tab>
                    @endif
                </div>
            </nav>
        @endauth

        <main class="page-fade @auth app-shell pb-28 pt-5 md:py-8 @else grid min-h-screen place-items-center px-3 py-5 sm:px-4 sm:py-10 @endauth">
            @yield('content')
        </main>
    </body>
</html>
