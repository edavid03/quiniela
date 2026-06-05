@component('mail::message')
# Nuevo contacto desde la web

**Nombre:** {{ $name }}

**Email:** {{ $email }}

**Teléfono:** {{ $phone }}

**Plan de interés:** {{ $plan }}

**Mejor horario:** {{ $horario }}

---

{{ $message }}

@component('mail::button', ['url' => 'mailto:'.$email])
Responder
@endcomponent
@endcomponent
