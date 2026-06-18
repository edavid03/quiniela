<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Telescope graba TODAS las peticiones; sin poda las tablas crecen sin limite.
// Requiere que el scheduler corra en prod (cron `schedule:run` o `schedule:work`).
Schedule::command('telescope:prune --hours=48')->daily();

// Importa resultados del Mundial y finaliza los partidos terminados. Una sola
// request por corrida; cada 30s = 2 req/min, holgado bajo el limite del tier
// free de football-data.org (10 req/min). Requiere schedule:work (scheduling
// sub-minuto), que ya usa el servicio `scheduler` en docker-compose.yml.
// Sincrono (no runInBackground): asi schedule:run espera a que termine y el
// estado (last_run) se persiste siempre; ademas evita mutex colgados si un
// reinicio mata un proceso en background. El expiry del mutex es de respaldo.
// OJO: el intervalo (30s) se refleja en la card; mantener en sync con
// AdminPartidoResultadoController::SYNC_INTERVAL_SECONDS.
Schedule::command('partidos:sync')
    ->everyThirtySeconds()
    ->withoutOverlapping(1);
