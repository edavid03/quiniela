@props(['date'])

{{-- Hora oficial de Venezuela (Caracas) fija para todos. Se renderiza server-side
     desde el instante UTC; la logica de cierre de pronosticos sigue en UTC aparte. --}}
@php
    $caracas = \Carbon\Carbon::parse($date, 'UTC')->setTimezone('America/Caracas');
@endphp

<time datetime="{{ $caracas->toIso8601String() }}">{{ $caracas->format('d/m/Y H:i') }} <span class="text-[var(--app-muted)]">VET</span></time>
