<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Canales de contacto / redes
    |--------------------------------------------------------------------------
    |
    | Se muestran en la landing (footer + sección contacto) solo si tienen valor.
    | - whatsapp: número en formato internacional, solo dígitos (ej. 5491122334455).
    | - instagram / x: usuario (con o sin @) o la URL completa.
    | El email cae a config('mail.contact_to').
    |
    */

    'email' => env('CONTACT_EMAIL', 'contacto@whaleecoding.com'),

    'whatsapp' => env('CONTACT_WHATSAPP'),

    'instagram' => env('CONTACT_INSTAGRAM'),

    'x' => env('CONTACT_X'),

    /*
    |--------------------------------------------------------------------------
    | Catálogos del formulario de contacto
    |--------------------------------------------------------------------------
    |
    | Única fuente de verdad para el <select> de la landing y la validación del
    | ContactController. La clave (izquierda) es lo que se valida con Rule::in;
    | la etiqueta (derecha) es lo que se muestra y lo que viaja en el correo.
    |
    */

    'plans' => [
        'gratis' => 'Gratis — 5 usuarios',
        '10' => '$10 — 20 usuarios',
        '25' => '$25 — 50 usuarios',
        '50' => '$50 — 120 usuarios',
        '80' => '$80 — usuarios infinitos',
        'asesoria' => 'No estoy seguro, quiero asesoría',
    ],

    'horarios' => [
        'asap' => 'Lo antes posible',
        'manana' => 'Mañana (8am–12pm)',
        'tarde' => 'Tarde (12pm–6pm)',
        'noche' => 'Noche (6pm–9pm)',
    ],

];
