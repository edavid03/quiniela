@extends('layouts.app')

@section('title', 'Dashboard | '.config('app.name', 'Quiniela'))

@section('content')
    @if (session('security_alert'))
        <div class="alert border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
            {{ session('security_alert') }}
        </div>
    @endif

    <section class="mb-6 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
        <div>
            <span class="kicker">FWC26 Quiniela</span>
            <h1 class="page-heading mt-3 md:text-6xl">Mesa de la quiniela</h1>
            <p class="mt-3 max-w-2xl text-base font-semibold leading-7 text-[var(--app-muted)] sm:text-lg">Pronosticos, partidos y ranking del grupo con una identidad inspirada en Monterrey 2026.</p>
        </div>
        <div class="action-row lg:justify-end">
            @unless (auth()->user()->isLigaAdmin())
                <a class="btn btn-primary" href="{{ route('liga.pronosticos.edit', ['liga' => $currentLiga]) }}">Crear o editar pronosticos</a>
            @endunless
            <a class="btn btn-secondary" href="{{ route('liga.rankings.index', ['liga' => $currentLiga]) }}">Ver ranking</a>
            @if (auth()->user()->isLigaAdmin())
                <a class="btn btn-secondary" href="{{ route('liga.admin.users.index', ['liga' => $currentLiga]) }}">Administrar liga</a>
            @endif
        </div>
    </section>

    <section class="mb-6 grid gap-4 md:grid-cols-3">
        <article class="stat-tile" data-mark="48">
            <span class="text-sm font-black uppercase text-[var(--app-muted)]">Equipos</span>
            <strong class="relative z-10 mt-3 block font-display text-5xl font-black text-[var(--app-text)]">{{ $teamCount }}</strong>
        </article>
        <article class="stat-tile" data-mark="26">
            <span class="text-sm font-black uppercase text-[var(--app-muted)]">Partidos</span>
            <strong class="relative z-10 mt-3 block font-display text-5xl font-black text-[var(--app-text)]">{{ $matchCount }}</strong>
        </article>
        @if (auth()->user()->isLigaAdmin())
            <article class="stat-tile" data-mark="3">
                <span class="text-sm font-black uppercase text-[var(--app-muted)]">Jugadores</span>
                <strong class="relative z-10 mt-3 block font-display text-5xl font-black text-[var(--app-text)]">{{ $playerCount }}</strong>
            </article>
        @else
            <article class="stat-tile" data-mark="3">
                <span class="text-sm font-black uppercase text-[var(--app-muted)]">Mis pronosticos</span>
                <strong class="relative z-10 mt-3 block font-display text-5xl font-black text-[var(--app-text)]">{{ $predictionCount }}</strong>
            </article>
        @endif
    </section>


    <div class="grid gap-5 lg:grid-cols-[1.35fr_.65fr]">
        <section class="surface overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--app-border)] px-5 py-4">
                <div>
                    <span class="kicker">Calendario</span>
                    <h2 class="font-display text-xl font-black">Proximos partidos</h2>
                </div>
                <span class="rounded-lg bg-[var(--app-panel-soft)] px-3 py-2 text-sm font-extrabold text-[var(--app-muted)]">{{ auth()->user()->username ?? 'usuario' }}</span>
            </div>

            @forelse ($nextMatches as $match)
                <article class="match-row lg:grid-cols-[1fr_13rem] lg:items-center">
                    <div class="team-line">
                        <span class="flag-chip">{!! $match->local?->flagEmojiHtml() !!}</span>
                        <span class="team-name">{{ $match->local->name ?? 'Local' }}</span>
                        <span class="mx-2 rounded-full bg-[var(--app-secondary)] px-2 py-1 text-xs font-black text-white">vs</span>
                        <span class="flag-chip">{!! $match->visitante?->flagEmojiHtml() !!}</span>
                        <span class="team-name">{{ $match->visitante->name ?? 'Visitante' }}</span>
                    </div>
                    <div class="text-sm font-semibold leading-6 text-[var(--app-muted)] lg:text-right">
                        <x-local-time :date="$match->fecha_utc" /><br>
                        {{ $match->estadio }}
                    </div>
                </article>
            @empty
                <div class="px-5 py-6 font-semibold text-[var(--app-muted)]">Todavia no hay partidos cargados.</div>
            @endforelse
        </section>

        <aside class="grid gap-5">
            {{-- INICIO CONTADOR REGRESIVO PRONOSTICOS: puedes editar o eliminar esta card completa. --}}
            <article class="stat-tile" data-mark="7">
                <span class="text-sm font-black uppercase text-[var(--app-muted)]">Proximo cierre de pronosticos</span>

                @if ($predictionDeadline)
                    <div
                        class="relative z-10 mt-3"
                        data-prediction-countdown
                        data-deadline="{{ $predictionDeadline->copy()->utc()->toIso8601String() }}"
                    >
                        <div class="countdown-grid">
                            <div class="countdown-cell">
                                <strong class="countdown-value" data-countdown-days>--</strong>
                                <span class="countdown-label">Dias</span>
                            </div>
                            <div class="countdown-cell">
                                <strong class="countdown-value" data-countdown-hours>--</strong>
                                <span class="countdown-label">Horas</span>
                            </div>
                            <div class="countdown-cell">
                                <strong class="countdown-value" data-countdown-minutes>--</strong>
                                <span class="countdown-label">Minutos</span>
                            </div>
                            <div class="countdown-cell">
                                <strong class="countdown-value" data-countdown-seconds>--</strong>
                                <span class="countdown-label">Segundos</span>
                            </div>
                        </div>
                        <p class="mt-2 text-xs font-extrabold text-[var(--app-secondary)]" data-countdown-status>Pronosticos abiertos</p>
                    </div>
                @else
                    <p class="relative z-10 mt-3 text-sm font-semibold leading-6 text-[var(--app-muted)]">No hay partidos con pronosticos abiertos.</p>
                @endif
            </article>
            {{-- FIN CONTADOR REGRESIVO PRONOSTICOS --}}

            <section class="surface overflow-hidden">
                <div class="border-b border-[var(--app-border)] px-5 py-3">
                    <span class="kicker">Reglas</span>
                    <h2 class="font-display text-lg font-black">Puntos</h2>
                </div>
                <div class="divide-y divide-[var(--app-border)] px-5">
                    @foreach ([['3', 'Marcador exacto', 'Goles de ambos equipos.'], ['1', 'Resultado correcto', 'Ganador.'], ['0', 'Sin acierto', 'Resultado distinto.']] as [$points, $title, $copy])
                        <div class="grid grid-cols-[2.25rem_1fr] gap-3 py-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-[var(--app-primary)] font-display text-sm font-black text-white dark:text-[#170f2f]">{{ $points }}</span>
                            <div>
                                <strong class="block font-display text-sm">{{ $title }}</strong>
                                <span class="text-xs font-semibold text-[var(--app-muted)]">{{ $copy }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    {{-- INICIO SCRIPT CONTADOR REGRESIVO PRONOSTICOS: puedes editar o eliminar este script completo. --}}
    @if ($predictionDeadline)
        <script>
            (() => {
                const countdown = document.querySelector('[data-prediction-countdown]');

                if (! countdown) {
                    return;
                }

                const deadline = new Date(countdown.dataset.deadline).getTime();

                if (Number.isNaN(deadline)) {
                    return;
                }

                const fields = {
                    days: countdown.querySelector('[data-countdown-days]'),
                    hours: countdown.querySelector('[data-countdown-hours]'),
                    minutes: countdown.querySelector('[data-countdown-minutes]'),
                    seconds: countdown.querySelector('[data-countdown-seconds]'),
                    status: countdown.querySelector('[data-countdown-status]'),
                };

                const twoDigits = (value) => String(value).padStart(2, '0');

                const renderCountdown = () => {
                    const remaining = deadline - Date.now();

                    if (remaining <= 0) {
                        fields.days.textContent = '00';
                        fields.hours.textContent = '00';
                        fields.minutes.textContent = '00';
                        fields.seconds.textContent = '00';
                        fields.status.textContent = 'Pronosticos cerrados';
                        return;
                    }

                    const secondsTotal = Math.floor(remaining / 1000);
                    const days = Math.floor(secondsTotal / 86400);
                    const hours = Math.floor((secondsTotal % 86400) / 3600);
                    const minutes = Math.floor((secondsTotal % 3600) / 60);
                    const seconds = secondsTotal % 60;

                    fields.days.textContent = twoDigits(days);
                    fields.hours.textContent = twoDigits(hours);
                    fields.minutes.textContent = twoDigits(minutes);
                    fields.seconds.textContent = twoDigits(seconds);
                    fields.status.textContent = 'Pronosticos abiertos';
                };

                renderCountdown();
                window.setInterval(renderCountdown, 1000);
            })();
        </script>
    @endif
    {{-- FIN SCRIPT CONTADOR REGRESIVO PRONOSTICOS --}}
@endsection
