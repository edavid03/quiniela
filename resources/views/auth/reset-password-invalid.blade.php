@extends('layouts.app')

@section('title', 'Enlace no valido | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="surface-strong w-full max-w-lg p-5 text-center sm:p-8">
        <h1 class="font-display text-3xl font-black text-[var(--app-text)]">Enlace no v&aacute;lido</h1>
        <p class="mt-3 font-semibold leading-6 text-[var(--app-muted)]">El enlace de recuperaci&oacute;n no existe, ya fue usado o expir&oacute;. Puedes pedir uno nuevo.</p>
        <a href="{{ route('liga.password.request', ['liga' => $liga]) }}" class="btn btn-primary mt-6 w-full">Pedir otro enlace</a>
    </section>
@endsection
