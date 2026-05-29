@extends('layouts.app')

@section('title', 'Invitación inválida | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="surface-strong w-full max-w-md p-6 text-center sm:p-8">
        <h1 class="font-display text-2xl font-black leading-tight text-[var(--app-text)] sm:text-3xl">Invitación no válida</h1>
        <p class="mt-3 font-semibold leading-6 text-[var(--app-muted)]">Este enlace ya fue usado, expiró o no existe. Pídele a tu administrador que te reenvíe la invitación.</p>
        <a href="{{ route('liga.login', ['liga' => $liga]) }}" class="btn btn-secondary mt-6">Ir al login</a>
    </section>
@endsection
