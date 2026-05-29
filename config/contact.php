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

    'email' => env('CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com')),

    'whatsapp' => env('CONTACT_WHATSAPP'),

    'instagram' => env('CONTACT_INSTAGRAM'),

    'x' => env('CONTACT_X'),

];
