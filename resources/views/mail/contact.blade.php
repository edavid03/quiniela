@component('mail::message')
# Nuevo contacto desde la web

**Nombre:** {{ $senderName }}

**Email:** {{ $senderEmail }}

---

{{ $messageBody }}

@component('mail::button', ['url' => 'mailto:'.$senderEmail])
Responder
@endcomponent
@endcomponent
