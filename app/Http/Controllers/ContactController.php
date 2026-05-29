<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        // Honeypot: el campo 'website' es invisible para humanos. Si viene lleno
        // es un bot -> se descarta en silencio (misma respuesta que un envio ok).
        if (filled($request->input('website'))) {
            return back()->with('status', 'Mensaje enviado, te contactamos pronto.');
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

        return back()->with('status', 'Mensaje enviado, te contactamos pronto.');
    }
}
