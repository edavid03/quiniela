@props(['ultimaSync' => null, 'intervalSeconds' => 30])

@php
    // Estado derivado del last_run REAL, no de un reloj independiente. Sano si el
    // ultimo sync esta dentro de ~1 intervalo de gracia (tolera el jitter del
    // scheduler); si quedo viejo, la card avisa "desfasada" en vez de mostrar un
    // contador inventado. El JS recalcula esto cada segundo; el render inicial
    // evita el flash de color y deja un estado coherente sin JS.
    $lastSyncEpoch = $ultimaSync?->getTimestamp();
    $initialState = $lastSyncEpoch === null
        ? 'waiting'
        : (now()->getTimestamp() - $lastSyncEpoch <= $intervalSeconds * 2 ? 'healthy' : 'stale');
    $cadenceLabel = $intervalSeconds < 60
        ? $intervalSeconds.' s'
        : intdiv($intervalSeconds, 60).' min';
@endphp

<section
    data-sync-card
    data-last-sync="{{ $lastSyncEpoch ?? '' }}"
    data-interval="{{ $intervalSeconds }}"
    data-state="{{ $initialState }}"
    class="group/sync mb-5 flex flex-col gap-3 rounded-xl border px-4 py-3 text-sm transition-colors sm:flex-row sm:items-center sm:justify-between
        border-[var(--app-border)] bg-[var(--app-surface)]
        data-[state=healthy]:border-emerald-200 data-[state=healthy]:bg-emerald-50 dark:data-[state=healthy]:border-emerald-900/50 dark:data-[state=healthy]:bg-emerald-950/30
        data-[state=stale]:border-amber-200 data-[state=stale]:bg-amber-50 dark:data-[state=stale]:border-amber-900/50 dark:data-[state=stale]:bg-amber-950/30">

    <div class="flex items-center gap-2.5 font-bold">
        <span class="relative flex h-2.5 w-2.5">
            <span class="absolute hidden h-full w-full rounded-full bg-emerald-400 opacity-75 group-data-[state=healthy]/sync:inline-flex group-data-[state=healthy]/sync:animate-ping"></span>
            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[var(--app-muted)] group-data-[state=healthy]/sync:bg-emerald-500 group-data-[state=stale]/sync:bg-amber-500"></span>
        </span>
        <span
            data-sync-title
            class="text-[var(--app-muted)] group-data-[state=healthy]/sync:text-[var(--app-success)] group-data-[state=stale]/sync:text-amber-600 dark:group-data-[state=stale]/sync:text-amber-400">
            @switch($initialState)
                @case('healthy') Sincronización automática activa @break
                @case('stale') Sincronización desfasada @break
                @default Esperando primera sincronización
            @endswitch
        </span>
    </div>

    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[var(--app-muted)]">
        <span>Última: <strong class="font-semibold text-[var(--app-text)]" data-sync-last>{{ $ultimaSync?->diffForHumans() ?? 'sin datos aún' }}</strong></span>
        <span data-sync-next>
            @if ($initialState === 'healthy')
                Próxima en <strong class="font-mono font-semibold text-[var(--app-text)]" data-sync-countdown>--:--</strong>
            @elseif ($initialState === 'stale')
                actualización pendiente
            @else
                en cuanto corra el scheduler
            @endif
        </span>
        <span class="text-xs">· cada {{ $cadenceLabel }}</span>
    </div>
</section>

<script>
    (function () {
        var card = document.querySelector('[data-sync-card]');
        if (!card) return;

        var raw = card.getAttribute('data-last-sync');
        var interval = parseInt(card.getAttribute('data-interval'), 10) || 30;
        var grace = interval; // toleramos ~1 intervalo de jitter del scheduler
        var lastSync = raw ? parseInt(raw, 10) : null;

        var titleEl = card.querySelector('[data-sync-title]');
        var lastEl = card.querySelector('[data-sync-last]');
        var nextEl = card.querySelector('[data-sync-next]');

        function pad(n) { return String(n).padStart(2, '0'); }

        function formatAgo(sec) {
            if (sec < 5) return 'recién';
            if (sec < 60) return 'hace ' + sec + ' s';
            if (sec < 3600) return 'hace ' + Math.floor(sec / 60) + ' min';
            if (sec < 86400) return 'hace ' + Math.floor(sec / 3600) + ' h';
            return 'hace ' + Math.floor(sec / 86400) + ' d';
        }

        function tick() {
            var now = Math.floor(Date.now() / 1000);

            if (lastSync === null) {
                card.setAttribute('data-state', 'waiting');
                titleEl.textContent = 'Esperando primera sincronización';
                lastEl.textContent = 'sin datos aún';
                nextEl.textContent = 'en cuanto corra el scheduler';
                return;
            }

            var age = now - lastSync;
            lastEl.textContent = formatAgo(age);

            // Sano: contador real al proximo limite alineado al epoch (para 30s
            // coincide con los segundos :00/:30 en que dispara el scheduler).
            if (age <= interval + grace) {
                card.setAttribute('data-state', 'healthy');
                titleEl.textContent = 'Sincronización automática activa';
                var remaining = interval - (now % interval);
                nextEl.innerHTML = 'Próxima en <strong class="font-mono font-semibold text-[var(--app-text)]">'
                    + pad(Math.floor(remaining / 60)) + ':' + pad(remaining % 60) + '</strong>';
                return;
            }

            // Desfasado: el sync no corrio a tiempo. Nada de contador inventado.
            card.setAttribute('data-state', 'stale');
            titleEl.textContent = 'Sincronización desfasada';
            nextEl.textContent = 'actualización pendiente';
        }

        tick();
        setInterval(tick, 1000);
    })();
</script>
