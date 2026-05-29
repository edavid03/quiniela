<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Superadmin | '.config('app.name', 'Quiniela'))</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
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
        @auth
            <header class="sticky top-0 z-30 border-b border-[var(--app-border)] bg-[var(--app-panel)]/88 backdrop-blur-xl">
                <div class="app-shell flex items-center justify-between gap-2 py-2 md:gap-4 md:py-3">
                    <a href="{{ route('superadmin.ligas.index') }}" class="flex min-w-0 items-center gap-2 no-underline md:gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-1 md:h-12 md:w-12">
                            <img src="{{ asset('images/fifa-world-cup-2026.svg') }}" alt="FIFA World Cup 2026" class="h-full w-full object-contain dark:brightness-0 dark:invert">
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate font-display text-sm font-black leading-tight text-[var(--app-text)] md:text-lg">Panel Superadmin</span>
                            <span class="hidden text-xs font-extrabold uppercase text-[var(--app-muted)] md:block">Control central</span>
                        </span>
                    </a>

                    <nav class="flex flex-wrap items-center gap-2 text-sm">
                        <a href="{{ route('superadmin.ligas.index') }}" class="btn btn-secondary {{ request()->routeIs('superadmin.ligas.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Ligas</a>
                        <a href="{{ route('superadmin.resultados.edit') }}" class="btn btn-secondary {{ request()->routeIs('superadmin.resultados.*') ? 'border-[var(--app-primary)] bg-[var(--app-panel-soft)]' : '' }}">Resultados</a>
                        <form method="POST" action="{{ route('superadmin.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary">Salir</button>
                        </form>
                    </nav>
                </div>
            </header>
        @endauth

        <main class="page-fade @auth app-shell py-6 md:py-8 @else grid min-h-screen place-items-center px-3 py-5 sm:px-4 sm:py-10 @endauth">
            @yield('content')
        </main>
    </body>
</html>
