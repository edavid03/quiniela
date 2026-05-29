<?php

namespace App\Http\Middleware;

use App\Models\Liga;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentLiga
{
    public function handle(Request $request, Closure $next): Response
    {
        $param = $request->route('liga');

        $liga = $param instanceof Liga
            ? $param
            : Liga::query()->where('slug', $param)->first();

        if ($liga === null) {
            abort(404);
        }

        if (! $liga->is_active) {
            abort(403, 'Esta liga esta deshabilitada.');
        }

        Tenancy::set($liga);

        // Guard cross-liga: un usuario logueado de otra liga no puede operar aca.
        // (En sesion real el LigaScope ya lo deja como invitado; este chequeo
        // cubre el acceso explicito y deja el 403 claro.)
        $user = $request->user();
        if ($user !== null && ! $user->isSuperAdmin() && $user->liga_id !== $liga->id) {
            abort(403, 'No perteneces a esta liga.');
        }

        // Asegura que controllers (type-hint Liga) y los helpers route() reciban
        // el modelo ya resuelto, sin depender del orden de SubstituteBindings.
        $request->route()->setParameter('liga', $liga);

        // Disponible en todas las vistas del tenant para armar los route().
        view()->share('currentLiga', $liga);

        return $next($request);
    }
}
