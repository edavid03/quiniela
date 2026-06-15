@extends('layouts.superadmin')

@section('title', 'Resultados | '.config('app.name', 'Quiniela'))

@section('content')
    <form method="POST" action="{{ route('superadmin.resultados.update') }}">
        @csrf

        <section class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="page-heading">Resultados de partidos</h1>
                <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Solo el administrador puede cargar o actualizar resultados. Al guardar, se recalculan los puntos de los pron&oacute;sticos.</p>
            </div>
            <div class="action-row">
                <a href="{{ route('superadmin.ligas.index') }}" class="btn btn-secondary">Volver</a>
                @if ($partidos->isNotEmpty())
                    <button class="btn btn-primary" type="submit">Guardar resultados</button>
                @endif
            </div>
        </section>

        <x-sync-status :ultima-sync="$ultimaSync" />

        @if (session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 font-bold text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30">{{ session('status') }}</div>
        @endif

        @if (session('security_alert'))
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 font-bold text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">{{ session('security_alert') }}</div>
        @elseif ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 font-bold text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">{{ $errors->first() }}</div>
        @endif

        <section class="surface overflow-hidden">
            @forelse ($partidos as $partido)
                <article class="match-row last:border-b-0 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <div class="team-line">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--app-panel-soft)]">{!! $partido->local?->flagEmojiHtml() !!}</span>
                            <span class="team-name">{{ $partido->local->name ?? 'Local' }}</span>
                            <span class="mx-2 rounded-full bg-[var(--app-secondary)] px-2 py-1 text-xs font-black text-white">vs</span>
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--app-panel-soft)]">{!! $partido->visitante?->flagEmojiHtml() !!}</span>
                            <span class="team-name">{{ $partido->visitante->name ?? 'Visitante' }}</span>
                        </div>
                        <div class="mt-2 text-sm leading-6 text-[var(--app-muted)]">
                            <x-local-time :date="$partido->fecha_utc" />
                            @if ($partido->estadio)
                                · {{ $partido->estadio }}
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col items-stretch gap-2">
                        <div class="score-grid">
                            <input name="resultados[{{ $partido->id }}][goles_local]" type="number" min="0" max="99" value="{{ old("resultados.{$partido->id}.goles_local", $partido->goles_local) }}" class="w-full rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] px-3 py-2.5 text-center text-[var(--app-text)] outline-none focus:border-[var(--app-primary)] focus:ring-4 focus:ring-[var(--app-ring)]">
                            <span class="text-center font-extrabold text-[var(--app-muted)]">-</span>
                            <input name="resultados[{{ $partido->id }}][goles_visitante]" type="number" min="0" max="99" value="{{ old("resultados.{$partido->id}.goles_visitante", $partido->goles_visitante) }}" class="w-full rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] px-3 py-2.5 text-center text-[var(--app-text)] outline-none focus:border-[var(--app-primary)] focus:ring-4 focus:ring-[var(--app-ring)]">
                        </div>

                        @if ($partido->resultado_origen === \App\Models\Partido::ORIGEN_API)
                            <span class="inline-flex items-center justify-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.5 7.5a1 1 0 0 1-1.42 0l-3.5-3.5a1 1 0 1 1 1.414-1.414l2.79 2.79 6.79-6.79a1 1 0 0 1 1.414 0Z" clip-rule="evenodd"/></svg>
                                Oficial (API)
                            </span>
                        @elseif ($partido->resultado_origen === \App\Models\Partido::ORIGEN_MANUAL)
                            <span class="inline-flex items-center justify-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300" title="Cargado a mano. El sync automático no lo sobrescribe.">
                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4 4 0 0 0-4 4v2H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-1V5a4 4 0 0 0-4-4Zm2 6V5a2 2 0 1 0-4 0v2h4Z" clip-rule="evenodd"/></svg>
                                Manual
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center rounded-full bg-[var(--app-panel-soft)] px-2.5 py-1 text-xs font-semibold text-[var(--app-muted)]">
                                Pendiente
                            </span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="px-5 py-6 text-[var(--app-muted)]">Todav&iacute;a no hay partidos cargados.</div>
            @endforelse
        </section>
    </form>
@endsection
