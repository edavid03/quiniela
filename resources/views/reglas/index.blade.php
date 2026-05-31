@extends('layouts.app')

@section('title', 'Reglas | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
        <div>
            <span class="kicker">Transparencia del juego</span>
            <h1 class="page-heading mt-3 md:text-5xl">Reglas y desempates</h1>
            <p class="mt-3 max-w-3xl text-base font-semibold leading-7 text-[var(--app-muted)]">
                Consulta como se calculan los puntos, que metricas ordenan el ranking y que ocurre cuando dos usuarios quedan igualados.
            </p>
        </div>
        <div class="action-row lg:justify-end">
            <a href="{{ route('liga.rankings.index', ['liga' => $currentLiga]) }}" class="btn btn-primary">Ver ranking</a>
            <a href="{{ route('liga.dashboard', ['liga' => $currentLiga]) }}" class="btn btn-secondary">Volver a mesa</a>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[.92fr_1.08fr]">
        <section class="surface overflow-hidden">
            <div class="border-b border-[var(--app-border)] px-5 py-4">
                <span class="kicker">Puntuacion</span>
                <h2 class="font-display text-xl font-black">Como suma cada pronostico</h2>
            </div>

            <div class="divide-y divide-[var(--app-border)] px-5">
                @foreach ([
                    ['3', 'Marcador exacto', 'El usuario acierta los goles de ambos equipos. Ejemplo: pronostica 2-1 y el partido termina 2-1.'],
                    ['1', 'Resultado correcto', 'El usuario acierta el signo del partido: gana local, gana visitante o empate, aunque el marcador sea distinto.'],
                    ['0', 'Sin acierto', 'El pronostico no coincide con el marcador exacto ni con el resultado general del partido.'],
                ] as [$points, $title, $copy])
                    <article class="grid grid-cols-[3rem_1fr] gap-4 py-5">
                        <span class="grid h-11 w-11 place-items-center rounded-lg bg-[var(--app-primary)] font-display text-lg font-black text-white shadow-[0_4px_0_color-mix(in_srgb,var(--app-primary-strong)_70%,#000)] dark:text-[#170f2f]">{{ $points }}</span>
                        <div>
                            <h3 class="font-display text-base font-black">{{ $title }}</h3>
                            <p class="mt-1 text-sm font-semibold leading-6 text-[var(--app-muted)]">{{ $copy }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="surface overflow-hidden">
            <div class="border-b border-[var(--app-border)] px-5 py-4">
                <span class="kicker">Ranking</span>
                <h2 class="font-display text-xl font-black">Metricas de desempate</h2>
            </div>

            <div class="grid gap-3 p-3 sm:p-4">
                @foreach ([
                    ['01', 'Puntos totales', 'Suma de todos los puntos obtenidos en pronosticos ya evaluados. Es el criterio principal.'],
                    ['02', 'Marcadores exactos', 'Cantidad de pronosticos que acertaron el marcador completo. Tiene prioridad si hay igualdad en puntos.'],
                    ['03', 'Pronosticos evaluados', 'Cantidad de pronosticos que ya tienen resultado procesado. Se usa si siguen empatados.'],
                    ['04', 'Nombre del usuario', 'Orden alfabetico ascendente para mantener una tabla estable cuando las metricas anteriores son iguales.'],
                ] as [$order, $title, $copy])
                    <article class="grid gap-3 rounded-lg border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-4 sm:grid-cols-[4.25rem_1fr]">
                        <span class="font-display text-3xl font-black leading-none text-[var(--app-secondary)]">{{ $order }}</span>
                        <div>
                            <h3 class="font-display text-base font-black">{{ $title }}</h3>
                            <p class="mt-1 text-sm font-semibold leading-6 text-[var(--app-muted)]">{{ $copy }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <section class="mt-5 surface-strong overflow-hidden">
        <div class="grid gap-4 border-l-8 border-[var(--app-secondary)] p-5 md:grid-cols-[1fr_auto] md:items-center">
            <div>
                <span class="kicker">Aviso de responsabilidad</span>
                <h2 class="mt-2 font-display text-2xl font-black">Software de gestion, no plataforma de apuestas</h2>
                <p class="mt-2 max-w-4xl text-sm font-semibold leading-6 text-[var(--app-muted)]">
                    Esta aplicacion funciona unicamente como herramienta de gestion, registro y visualizacion de pronosticos deportivos entre usuarios de una liga privada. No administra, promueve ni procesa apuestas, dinero, premios monetarios, pagos, cuotas, probabilidades ni operaciones de azar.
                </p>
            </div>
            <span class="rounded-lg bg-[var(--app-panel-soft)] px-4 py-3 text-center font-display text-sm font-black uppercase text-[var(--app-primary)]">Uso informativo</span>
        </div>
    </section>
@endsection
