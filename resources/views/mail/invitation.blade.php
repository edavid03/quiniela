@component('mail::message')
# Te sumaron a {{ $liga->name }}

Hola **{{ $username }}**, te invitaron a participar de la quiniela de **{{ $liga->name }}**.

Activa tu cuenta y elige tu contraseña con este botón:

@component('mail::button', ['url' => $url])
Activar mi cuenta
@endcomponent

Por seguridad, este enlace vence en 7 días. Si no esperabas esta invitación, ignora este correo.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
