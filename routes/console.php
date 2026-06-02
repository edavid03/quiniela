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
