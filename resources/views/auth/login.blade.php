@extends('layouts.app')

@section('title', 'Entrar | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="relative w-full max-w-6xl overflow-hidden rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] shadow-[0_30px_90px_rgba(18,11,36,.18)]">

        <div class="relative grid lg:min-h-[38rem] lg:grid-cols-[1.05fr_.95fr]">
            <aside class="relative overflow-hidden bg-[var(--fwc-primary)] p-5 text-[var(--fwc-aux-cream)] sm:p-8 lg:p-10">
                <div class="absolute inset-0 opacity-45" style="background-image: radial-gradient(circle at 18% 18%, color-mix(in srgb, var(--fwc-red) 42%, transparent), transparent 12rem);"></div>
                <div class="relative flex h-full flex-col justify-between gap-8 lg:gap-10">
                    <div>
                        <span class="grid h-20 w-20 place-items-center rounded-lg bg-white p-2.5 shadow-[0_8px_0_rgba(0,0,0,.22)] sm:h-28 sm:w-28 sm:p-3 sm:shadow-[0_12px_0_rgba(0,0,0,.22)]">
                            <img src="{{ asset('images/fifa-world-cup-2026.svg') }}" alt="FIFA World Cup 2026" class="h-full w-full object-contain" loading="eager" decoding="async">
                        </span>

                        <div class="mt-6 max-w-xl sm:mt-8">
                            <span class="inline-flex rounded-lg border border-white/20 bg-white/10 px-3 py-2 font-display text-[11px] font-black uppercase text-white sm:text-xs">Quiniela privada &middot; #SOMOS26</span>
                            <h1 class="mt-5 font-display text-4xl font-black leading-[1.02] text-white sm:text-5xl md:text-7xl">Vive cada marcador</h1>
                            <p class="mt-4 max-w-md text-base font-semibold leading-7 text-white/82 sm:mt-5 sm:text-lg">Una mesa de pronosticos con ritmo de torneo: calendario, puntos y ranking para seguir cada fecha del Mundial 2026.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <div class="rounded-lg border border-white/15 bg-white/10 p-4">
                            <span class="block font-display text-2xl font-black text-[var(--fwc-red)] sm:text-3xl">48</span>
                            <span class="text-xs font-extrabold text-white/78 sm:text-sm">equipos</span>
                        </div>
                        <div class="rounded-lg border border-white/15 bg-white/10 p-4">
                            <span class="block font-display text-2xl font-black text-[var(--fwc-aux-green)] sm:text-3xl">104</span>
                            <span class="text-xs font-extrabold text-white/78 sm:text-sm">partidos</span>
                        </div>
                        <div class="rounded-lg border border-white/15 bg-white/10 p-4">
                            <span class="block font-display text-2xl font-black text-[var(--fwc-aux-cream)] sm:text-3xl">3</span>
                            <span class="text-xs font-extrabold text-white/78 sm:text-sm">puntos exacto</span>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex items-center p-4 sm:p-8 lg:p-10">
                <section class="surface-strong w-full p-5 sm:p-8">
                    <div class="mb-8">
                       
                        <h2 class="mt-3 font-display text-2xl font-black leading-tight text-[var(--app-text)] sm:text-4xl">Entra a tu tablero</h2>
                        <p class="mt-3 max-w-md font-semibold leading-6 text-[var(--app-muted)]">Carga tus marcadores, consulta el ranking y vuelve cuando quieras a ajustar tus pronosticos.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="grid gap-5">
                        @csrf

                        <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="username">
                            Usuario
                            <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus autocomplete="username" class="rounded-lg px-4 py-3.5 text-base shadow-[inset_0_-3px_0_color-mix(in_srgb,var(--app-border)_45%,transparent)]" placeholder="tu_usuario">
                        </label>

                        <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="password">
                            Contrase&ntilde;a
                            <input id="password" name="password" type="password" required autocomplete="current-password" class="rounded-lg px-4 py-3.5 text-base shadow-[inset_0_-3px_0_color-mix(in_srgb,var(--app-border)_45%,transparent)]" placeholder="••••••••">
                        </label>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="flex items-center gap-2 text-sm font-bold text-[var(--app-muted)]">
                                <input name="remember" type="checkbox" value="1" class="h-4 w-4 accent-[var(--fwc-red)]">
                                Recordarme
                            </label>
                            <span class="rounded-lg bg-[var(--app-panel-soft)] px-3 py-2 text-xs font-black uppercase text-[var(--app-muted)]">FWC26</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            Entrar
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </section>
@endsection
