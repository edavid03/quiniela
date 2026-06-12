@extends('layouts.app')

@section('title', 'Pronosticos publicos | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <span class="kicker">Pronosticos de todos</span>
            <h1 class="page-heading mt-3 md:text-5xl">Pronosticos publicos</h1>
            <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Todos los pronosticos de los jugadores para los partidos ya finalizados.</p>
        </div>
        <div class="action-row">
            <a href="{{ route('liga.dashboard', ['liga' => $currentLiga]) }}" class="btn btn-secondary">Volver a mesa</a>
            <a href="{{ route('liga.rankings.index', ['liga' => $currentLiga]) }}" class="btn btn-primary">Ver ranking</a>
        </div>
    </section>

    <section class="surface overflow-hidden">
        <div class="grid grid-cols-[1fr_auto] items-center gap-3 border-b border-[var(--app-border)] px-5 py-4">
            <div>
                <span class="kicker">Resultados y apuestas</span>
                <h2 class="font-display text-xl font-black">Partidos finalizados</h2>
            </div>
            <span class="rounded-lg bg-[var(--app-panel-soft)] px-3 py-2 text-sm font-extrabold text-[var(--app-muted)]">{{ $partidos->count() }} partidos</span>
        </div>

        @forelse ($partidos->groupBy('fase') as $fase => $partidosFase)
            <div class="flex items-center justify-between gap-3 border-t border-[var(--app-border)] bg-[var(--app-panel-soft)] px-5 py-3 first:border-t-0">
                <span class="kicker">{{ $fase }}</span>
                <span class="text-xs font-extrabold text-[var(--app-muted)]">{{ $partidosFase->count() }}</span>
            </div>
            <div class="grid grid-cols-1 gap-3 p-3 sm:p-4">
                @foreach ($partidosFase as $partido)
                @php
                    $prediccionesPorUsuario = $partido->predicciones->keyBy('usuario_id');
                @endphp

                <article class="grid gap-3 rounded-lg border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-4 text-xs">
                    <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-1 text-center text-[11px] font-extrabold leading-tight">
                        <div class="min-w-0">
                            <span class="flag-chip mx-auto text-sm">{!! $partido->local?->flagEmojiHtml() !!}</span>
                            <span class="mt-1 block truncate">{{ $partido->local->name ?? 'Local' }}</span>
                        </div>
                        <span class="rounded-full bg-[var(--app-secondary)] px-1.5 py-0.5 text-[10px] font-black text-white">vs</span>
                        <div class="min-w-0">
                            <span class="flag-chip mx-auto text-sm">{!! $partido->visitante?->flagEmojiHtml() !!}</span>
                            <span class="mt-1 block truncate">{{ $partido->visitante->name ?? 'Visitante' }}</span>
                        </div>
                    </div>

                    <div class="text-center text-[10px] font-semibold leading-4 text-[var(--app-muted)]">
                        <x-local-time :date="$partido->fecha_utc" />
                        @if ($partido->estadio)
                            <br>{{ $partido->estadio }}
                        @endif
                    </div>

                    <div class="text-center">
                        <span class="text-[9px] font-black uppercase text-[var(--app-muted)]">Resultado oficial</span>
                        <div class="mt-1 font-display text-2xl font-black leading-none text-[var(--app-text)]">
                            {{ $partido->goles_local }} - {{ $partido->goles_visitante }}
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-[var(--app-border)] text-[9px] font-black uppercase text-[var(--app-muted)]">
                                    <th class="px-2 py-1.5">Jugador</th>
                                    <th class="px-2 py-1.5 text-center">Pronostico</th>
                                    <th class="px-2 py-1.5 text-center">Pts</th>
                                    <th class="px-2 py-1.5 text-right hidden sm:table-cell">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $jugadoresOrdenados = $users->sortByDesc(function ($user) use ($prediccionesPorUsuario) {
                                        $pred = $prediccionesPorUsuario->get($user->id);
                                        if (!$pred || $pred->puntos === null) return -1;
                                        return $pred->puntos;
                                    });
                                @endphp
                                @forelse ($jugadoresOrdenados as $user)
                                    @php
                                        $pred = $prediccionesPorUsuario->get($user->id);
                                        $tienePred = $pred !== null && $pred->goles_local !== null;
                                        $evaluada = $tienePred && $pred->puntos !== null;
                                        $clase = '';
                                        $estado = '';
                                        if (!$tienePred) {
                                            $clase = 'text-[var(--app-muted)] opacity-50';
                                            $estado = 'Sin pronostico';
                                        } elseif ($pred->puntos === 3) {
                                            $clase = 'bg-green-900/20 text-green-300';
                                            $estado = 'Exacto';
                                        } elseif ($pred->puntos === 1) {
                                            $clase = 'bg-yellow-900/20 text-yellow-300';
                                            $estado = 'Acerto resultado';
                                        } elseif ($pred->puntos === 0) {
                                            $clase = 'bg-red-900/20 text-red-300';
                                            $estado = 'Sin acierto';
                                        }
                                    @endphp
                                    <tr class="{{ $clase }} border-b border-[var(--app-border)] last:border-b-0 transition hover:brightness-110">
                                        <td class="px-2 py-1.5 font-bold">{{ $user->name }}</td>
                                        <td class="px-2 py-1.5 text-center font-display text-base font-black">
                                            @if ($tienePred)
                                                {{ $pred->goles_local }} - {{ $pred->goles_visitante }}
                                            @else
                                                <span class="text-[var(--app-muted)]">--</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-1.5 text-center font-display text-base font-black">
                                            @if ($evaluada)
                                                {{ $pred->puntos }}
                                            @else
                                                <span class="text-[var(--app-muted)]">--</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-1.5 text-right text-[10px] font-black uppercase hidden sm:table-cell">
                                            @if ($tienePred && $evaluada)
                                                {{ $estado }}
                                            @elseif ($tienePred)
                                                Por evaluar
                                            @else
                                                Sin pronostico
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-2 py-4 text-center font-semibold text-[var(--app-muted)]">
                                            No hay jugadores en esta liga.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
                @endforeach
            </div>
        @empty
            <div class="px-5 py-6 font-semibold text-[var(--app-muted)]">
                Todavia no hay partidos finalizados con resultados cargados.
            </div>
        @endforelse
    </section>
@endsection
