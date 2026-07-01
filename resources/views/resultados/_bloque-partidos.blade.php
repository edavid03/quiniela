<div class="flex items-center justify-between gap-3 border-t border-[var(--app-border)] bg-[var(--app-panel-soft)] px-5 py-3 first:border-t-0">
    <span class="kicker">{{ $titulo }}</span>
    <span class="text-xs font-extrabold text-[var(--app-muted)]">{{ $partidos->count() }}</span>
</div>
<div class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 sm:p-4 xl:grid-cols-3">
    @foreach ($partidos as $partido)
    @php
        $tieneResultado = $partido->goles_local !== null && $partido->goles_visitante !== null;
        $prediccion = $predicciones->get($partido->id);
        $tienePrediccion = $prediccion !== null;
        $prediccionEvaluada = $tienePrediccion && $prediccion->puntos !== null;
    @endphp

    <article class="grid min-h-40 gap-2 rounded-lg border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-3 text-xs">
        <div>
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
            <div class="mt-2 text-center text-[10px] font-semibold leading-4 text-[var(--app-muted)]">
                <x-local-time :date="$partido->fecha_utc" />
                @if ($partido->estadio)
                    <br>{{ $partido->estadio }}
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div class="rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] px-2 py-2 text-center">
                <div class="text-[9px] font-black uppercase text-[var(--app-muted)]">Resultado</div>
                @if ($tieneResultado)
                    <div class="mt-1 font-display text-xl font-black leading-none text-[var(--app-text)]">
                        {{ $partido->goles_local }} - {{ $partido->goles_visitante }}
                    </div>
                    <div class="mt-1 text-[9px] font-black uppercase text-[var(--app-success)]">Finalizado</div>
                @else
                    <div class="mt-1 font-display text-lg font-black leading-none text-[var(--app-muted)]">-- - --</div>
                    <div class="mt-1 text-[9px] font-black uppercase text-[var(--app-secondary)]">Pendiente</div>
                @endif
            </div>

            <div class="rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] px-2 py-2 text-center">
                <div class="text-[9px] font-black uppercase text-[var(--app-muted)]">Tu pronostico</div>
                @if ($tienePrediccion)
                    <div class="mt-1 font-display text-xl font-black leading-none text-[var(--app-text)]">
                        {{ $prediccion->goles_local }} - {{ $prediccion->goles_visitante }}
                    </div>
                    @if ($prediccionEvaluada)
                        <div class="mt-1 text-[9px] font-black uppercase {{ $prediccion->puntos > 0 ? 'text-[var(--app-success)]' : 'text-[var(--app-danger)]' }}">
                            {{ $prediccion->puntos > 0 ? 'Acertaste' : 'No acertaste' }}
                        </div>
                    @else
                        <div class="mt-1 text-[9px] font-black uppercase text-[var(--app-muted)]">Por evaluar</div>
                    @endif
                @else
                    <div class="mt-1 text-[11px] font-semibold leading-4 text-[var(--app-muted)]">Sin pronostico</div>
                @endif
            </div>
        </div>

        <div class="rounded-lg bg-[var(--app-panel-soft)] px-2 py-1.5 text-center text-[10px] font-black uppercase leading-tight text-[var(--app-muted)]">
            @if ($tienePrediccion && $prediccionEvaluada)
                {{ $prediccion->puntos }} pts
            @elseif ($tienePrediccion)
                Pendiente de resultado
            @else
                Sin pronostico registrado
            @endif
        </div>
    </article>
    @endforeach
</div>
