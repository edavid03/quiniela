@props(['ultimaSync' => null, 'intervalSeconds' => 30])

@php
    // Estado derivado del last_run REAL, no de un reloj independiente. Sano si el
    // ultimo sync esta dentro de ~1 intervalo de gracia (tolera el jitter del
    // scheduler); si quedo viejo, la card avisa "desfasada" en vez de mostrar un
    // contador inventado. El JS recalcula esto cada segundo y refresca el last_run
    // por polling; el render inicial evita el flash y deja un estado coherente sin JS.
    $lastSyncEpoch = $ultimaSync?->getTimestamp();
    $initialState = $lastSyncEpoch === null
        ? 'waiting'
        : (now()->getTimestamp() - $lastSyncEpoch <= $intervalSeconds * 2 ? 'healthy' : 'stale');
    $cadenceLabel = $intervalSeconds < 60
        ? $intervalSeconds.' s'
        : intdiv($intervalSeconds, 60).' min';
    $stateWord = ['healthy' => 'activa', 'stale' => 'desfasada', 'waiting' => 'sin datos'][$initialState];
@endphp

<section
    data-sync-card
    data-last-sync="{{ $lastSyncEpoch ?? '' }}"
    data-interval="{{ $intervalSeconds }}"
    data-state="{{ $initialState }}"
    data-sync-url="{{ route('superadmin.resultados.sync-status') }}"
    class="group/sync surface-strong mb-5 flex flex-col gap-4 rounded-xl p-4 sm:flex-row sm:items-center sm:justify-between">

    {{-- Estado --}}
    <div class="flex items-center gap-3">
        <span class="relative grid h-11 w-11 flex-none place-items-center rounded-xl bg-[var(--app-panel-soft)]">
            <span class="relative flex h-3 w-3">
                <span class="absolute hidden h-full w-full rounded-full bg-emerald-400 opacity-75 group-data-[state=healthy]/sync:inline-flex group-data-[state=healthy]/sync:animate-ping"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-[var(--app-muted)] group-data-[state=healthy]/sync:bg-emerald-500 group-data-[state=stale]/sync:bg-amber-500"></span>
            </span>
        </span>
        <div class="leading-tight">
            <p class="font-display font-extrabold text-[var(--app-text)]">Sincronización automática</p>
            <p class="text-xs text-[var(--app-muted)]">
                <span
                    data-sync-state-word
                    class="font-bold capitalize group-data-[state=healthy]/sync:text-[var(--app-success)] group-data-[state=stale]/sync:text-amber-600 dark:group-data-[state=stale]/sync:text-amber-400">{{ $stateWord }}</span>
                · cada {{ $cadenceLabel }}
            </p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="flex items-center gap-6 sm:gap-8">
        <div class="leading-tight">
            <p class="text-[0.65rem] font-bold uppercase tracking-wide text-[var(--app-muted)]">Última</p>
            <p class="mt-0.5 font-semibold text-[var(--app-text)]" data-sync-last>{{ $ultimaSync?->diffForHumans() ?? 'sin datos aún' }}</p>
        </div>
        <div class="leading-tight">
            <p class="text-[0.65rem] font-bold uppercase tracking-wide text-[var(--app-muted)]">Próxima</p>
            <p
                data-sync-next
                class="mt-0.5 font-mono font-bold tabular-nums text-[var(--app-text)] group-data-[state=stale]/sync:font-sans group-data-[state=stale]/sync:font-semibold group-data-[state=stale]/sync:text-amber-600 dark:group-data-[state=stale]/sync:text-amber-400">@switch($initialState)@case('healthy')--:--@break @case('stale')pendiente@break @default —@endswitch</p>
        </div>
    </div>
</section>

<script>
    (function () {
        var card = document.querySelector('[data-sync-card]');
        if (!card) return;

        var interval = parseInt(card.getAttribute('data-interval'), 10) || 30;
        var grace = interval; // toleramos ~1 intervalo de jitter del scheduler
        var raw = card.getAttribute('data-last-sync');
        var lastSync = raw ? parseInt(raw, 10) : null;
        var url = card.getAttribute('data-sync-url');

        var wordEl = card.querySelector('[data-sync-state-word]');
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

        function render() {
            var now = Math.floor(Date.now() / 1000);

            if (lastSync === null) {
                card.setAttribute('data-state', 'waiting');
                wordEl.textContent = 'sin datos';
                lastEl.textContent = 'sin datos aún';
                nextEl.textContent = '—';
                return;
            }

            var age = now - lastSync;
            lastEl.textContent = formatAgo(age);

            // Sano: contador real al proximo limite alineado al epoch (para 30s
            // coincide con los segundos :00/:30 en que dispara el scheduler).
            if (age <= interval + grace) {
                card.setAttribute('data-state', 'healthy');
                wordEl.textContent = 'activa';
                var remaining = interval - (now % interval);
                nextEl.textContent = pad(Math.floor(remaining / 60)) + ':' + pad(remaining % 60);
                return;
            }

            // Desfasado: el sync no corrio a tiempo. Nada de contador inventado.
            card.setAttribute('data-state', 'stale');
            wordEl.textContent = 'desfasada';
            nextEl.textContent = 'pendiente';
        }

        // Refresca el last_run real desde el server; sin esto la pagina abierta
        // cuenta sobre un valor viejo y termina marcando "desfasada" de mentira.
        function poll() {
            if (!url) return;
            fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (!data) return;
                    if (typeof data.interval_seconds === 'number') { interval = data.interval_seconds; grace = interval; }
                    if (data.last_run_epoch != null) lastSync = data.last_run_epoch;
                    render();
                })
                .catch(function () { /* mantener ultimo valor conocido */ });
        }

        render();
        setInterval(render, 1000);
        setInterval(poll, 10000);
    })();
</script>
