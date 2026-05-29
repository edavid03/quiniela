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

        // Solo los jugadores pronostican: el admin organiza la liga, no compite.
        if ($user === null || ! $user->isLigaUser()) {
            if ($request->expectsJson()) {
                abort(403);
            }

            return redirect()
                ->route('liga.dashboard', ['liga' => Tenancy::liga()])
                ->with('security_alert', 'Los administradores organizan la liga; no participan en los pronosticos.');
        }

        return $next($request);
    }
}
