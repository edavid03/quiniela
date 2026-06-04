<?php

use App\Http\Middleware\EnsureLigaAdmin;
use App\Http\Middleware\EnsureLigaPlayer;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\SetCurrentLiga;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'liga' => SetCurrentLiga::class,
            'liga.admin' => EnsureLigaAdmin::class,
            'liga.player' => EnsureLigaPlayer::class,
            'superadmin' => EnsureSuperAdmin::class,
            'no.cache' => PreventBackHistory::class,
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
        // El token CSRF rota al loguearse/activar cuenta; un doble-submit (o el
        // reintento de un webview in-app) llega con el token viejo -> 419. Laravel
        // ya convirtio el TokenMismatchException en HttpException(419), asi que
        // filtramos por status. En vez de la pantalla cruda "Page Expired",
        // volvemos al formulario con un aviso.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null; // el resto de errores HTTP siguen su curso normal
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'La página expiró. Recarga e intenta de nuevo.',
                ], 419);
            }

            return redirect()->back()->withErrors([
                'expired' => 'La página estuvo abierta demasiado tiempo y la sesión expiró. Vuelve a intentarlo.',
            ]);
        });
    })->create();
