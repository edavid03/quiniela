<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Evita que el navegador muestre paginas autenticadas desde el cache (incluido
     * el bfcache) al usar "atras" despues del logout. `no-store` fuerza a re-pedir
     * la pagina al server, que redirige al login si ya no hay sesion.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Via headers->set para soportar cualquier respuesta (incluido BinaryFileResponse
        // de las descargas, que no expone el helper ->header()).
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
