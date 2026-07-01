@extends('layouts.app')

@section('title', 'Resultados | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <span class="kicker">Marcadores</span>
            <h1 class="page-heading mt-3 md:text-5xl">Resultados de partidos</h1>
            <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Consulta todos los partidos cargados y los marcadores oficiales cuando el administrador los registre.</p>
        </div>
        <div class="action-row">
            <a href="{{ route('liga.dashboard', ['liga' => $currentLiga]) }}" class="btn btn-secondary">Volver a mesa</a>
            <a href="{{ route('liga.rankings.index', ['liga' => $currentLiga]) }}" class="btn btn-primary">Ver ranking</a>
        </div>
    </section>

    <section class="surface overflow-hidden">
        <div class="grid grid-cols-[1fr_auto] items-center gap-3 border-b border-[var(--app-border)] px-5 py-4">
            <div>
                <span class="kicker">Calendario completo</span>
                <h2 class="font-display text-xl font-black">Partidos y resultados</h2>
            </div>
            <span class="rounded-lg bg-[var(--app-panel-soft)] px-3 py-2 text-sm font-extrabold text-[var(--app-muted)]">{{ $totalPartidos }} partidos</span>
        </div>

        @if ($totalPartidos === 0)
            <div class="px-5 py-6 font-semibold text-[var(--app-muted)]">Todavia no hay partidos cargados.</div>
        @else
            @php
                $secciones = [
                    ['titulo' => 'Partidos de hoy', 'grupos' => $gruposHoy],
                    ['titulo' => 'Proximos partidos', 'grupos' => $gruposProximos],
                    ['titulo' => 'Partidos ya jugados', 'grupos' => $gruposJugados],
                ];
            @endphp

            @foreach ($secciones as $seccion)
                @continue($seccion['grupos']->isEmpty())
                <div class="flex items-center justify-between gap-3 border-t border-[var(--app-border)] bg-[var(--app-panel)] px-5 py-3 first:border-t-0">
                    <h3 class="font-display text-lg font-black">{{ $seccion['titulo'] }}</h3>
                    <span class="rounded-lg bg-[var(--app-panel-soft)] px-3 py-1.5 text-xs font-extrabold text-[var(--app-muted)]">{{ $seccion['grupos']->sum(fn ($g) => $g['partidos']->count()) }}</span>
                </div>
                @foreach ($seccion['grupos'] as $grupo)
                    @include('resultados._bloque-partidos', ['titulo' => $grupo['titulo'], 'partidos' => $grupo['partidos']])
                @endforeach
            @endforeach
        @endif
    </section>
@endsection
