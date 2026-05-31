@component('mail::message')
# Recupera tu clave en {{ $liga->name }}

Hola **{{ $username }}**, recibimos una solicitud para cambiar la contrase&ntilde;a de tu usuario en **{{ $liga->name }}**.

Usa este bot&oacute;n para crear una nueva clave:

@component('mail::button', ['url' => $url])
Cambiar mi clave
@endcomponent

Por seguridad, este enlace vence en 60 minutos. Si no solicitaste este cambio, ignora este correo.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
