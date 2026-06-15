<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le da a cada liga su propia cookie de sesión (nombre + path por slug), para
 * que dos ligas puedan estar logueadas a la vez en el mismo navegador sin
 * pisarse. Va PREPENDED al grupo `web`, así corre antes de StartSession, que
 * lee `session.cookie`/`session.path` en tiempo de ejecución.
 */
class AssignTenantSession
{
    /**
     * Primeros segmentos que NO son slug de liga (usan la cookie default).
     */
    private const RESERVED = ['superadmin', 'contacto', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->segment(1);

        if ($slug === null || $slug === '' || in_array($slug, self::RESERVED, true)) {
            return $next($request);
        }

        $path = '/'.$slug;
        $cookie = 'q_session_'.preg_replace('/[^A-Za-z0-9_\-]/', '_', $slug);

        config([
            'session.cookie' => $cookie,
            'session.path' => $path,
        ]);

        // El CookieJar es singleton: si ya se resolvió, su path quedó en '/'.
        // Lo forzamos para que el recaller de "recordarme" y cualquier cookie
        // encolada también queden scopeados al slug.
        app('cookie')->setDefaultPathAndDomain(
            $path,
            config('session.domain'),
            (bool) config('session.secure'),
            config('session.same_site'),
        );

        return $next($request);
    }
}
