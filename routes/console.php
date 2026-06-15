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
// request por corrida; cada 10 min queda holgado bajo el limite del tier free.
// Sincrono (no runInBackground): asi schedule:run espera a que termine y el
// estado (last_run) se persiste siempre; ademas evita mutex colgados si un
// reinicio mata un proceso en background. El expiry del mutex es de respaldo.
Schedule::command('partidos:sync')
    ->everyTenMinutes()
    ->withoutOverlapping(15);
