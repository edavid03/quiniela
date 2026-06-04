<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request): RedirectResponse|JsonResponse
    {
        // Honeypot: el campo 'website' es invisible para humanos. Si viene lleno
        // es un bot -> se descarta en silencio (misma respuesta que un envio ok).
        if (filled($request->input('website'))) {
            return $this->ok($request);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Mail::to(config('mail.contact_to'))->queue(new ContactMail(
            $data['name'],
            $data['email'],
            $data['message'],
        ));

        return $this->ok($request);
    }

    /**
     * Respuesta de exito: JSON para envios via fetch (sin recargar la pagina),
     * o redirect con flash para el envio clasico sin JS. Los errores de
     * validacion ya devuelven 422 JSON solos cuando la request espera JSON.
     */
    private function ok(Request $request): RedirectResponse|JsonResponse
    {
        $status = 'Mensaje enviado, te contactamos pronto.';

        if ($request->expectsJson()) {
            return response()->json(['status' => $status]);
        }

        return back()->with('status', $status);
    }
}
