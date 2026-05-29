<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLigaAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Debe ser admin de ESTA liga (no de otra): bloquea acceso horizontal.
        if ($user === null || ! $user->isLigaAdmin() || $user->liga_id !== Tenancy::id()) {
            if ($request->expectsJson()) {
                abort(403);
            }

            return redirect()
                ->route('liga.dashboard', ['liga' => Tenancy::liga()])
                ->with('security_alert', 'No tienes permisos para administrar esta liga.');
        }

        return $next($request);
    }
}
