<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLigaPlayer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || (! $user->isLigaUser() && ! $user->isLigaAdmin())) {
            if ($request->expectsJson()) {
                abort(403);
            }

            return redirect()
                ->route('liga.dashboard', ['liga' => Tenancy::liga()])
                ->with('security_alert', 'No tienes permisos para registrar pronosticos en esta liga.');
        }

        return $next($request);
    }
}
