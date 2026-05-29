<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'liga' => \App\Http\Middleware\SetCurrentLiga::class,
            'liga.admin' => \App\Http\Middleware\EnsureLigaAdmin::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);

        // No hay login global: el destino depende del area (superadmin vs liga).
        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('superadmin') || $request->is('superadmin/*')) {
                return route('superadmin.login');
            }

            $slug = $request->segment(1);

            return $slug
                ? route('liga.login', ['liga' => $slug])
                : route('superadmin.login');
        });

        // Usuario ya logueado que pega a una ruta de invitado -> su area.
        $middleware->redirectUsersTo(function (Request $request): string {
            $user = $request->user();

            if ($user?->isSuperAdmin()) {
                return route('superadmin.ligas.index');
            }

            return $user?->liga
                ? route('liga.dashboard', ['liga' => $user->liga->slug])
                : route('superadmin.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
