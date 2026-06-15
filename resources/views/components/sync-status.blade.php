@props(['ultimaSync' => null])

<section class="mb-5 flex flex-col gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm dark:border-emerald-900/50 dark:bg-emerald-950/30 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-2 font-bold text-[var(--app-success)]">
        <span class="relative flex h-2.5 w-2.5">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
        </span>
        Sincronización automática activa
    </div>

    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[var(--app-muted)]">
        <span>Última: <strong class="font-semibold text-[var(--app-text)]">{{ $ultimaSync?->diffForHumans() ?? 'sin datos aún' }}</strong></span>
        <span>Próxima en <strong class="font-mono font-semibold text-[var(--app-text)]" data-sync-countdown>--:--</strong></span>
        <span class="text-xs">· cada 10 min</span>
    </div>
</section>

<script>
    (function () {
        var el = document.querySelector('[data-sync-countdown]');
        if (!el) return;

        function tick() {
            // El cron corre en UTC en los multiplos de 600s del epoch (=:00,:10,...),
            // asi que el contador es exacto e independiente de la zona horaria.
            var remaining = 600 - (Math.floor(Date.now() / 1000) % 600);
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            el.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }

        tick();
        setInterval(tick, 1000);
    })();
</script>
