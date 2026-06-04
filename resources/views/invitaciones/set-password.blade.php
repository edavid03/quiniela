@extends('layouts.app')

@section('title', 'Activar cuenta | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="surface-strong w-full max-w-md p-6 sm:p-8">
        <div class="mb-5">
            <span class="kicker">{{ $liga->name }}</span>
            <h1 class="mt-3 font-display text-2xl font-black leading-tight text-[var(--app-text)] sm:text-3xl">Activa tu cuenta</h1>
            <p class="mt-2 font-semibold leading-6 text-[var(--app-muted)]">Elige tu contraseña para entrar a la quiniela de <strong>{{ $liga->name }}</strong>.</p>
        </div>

        <div class="mb-6 rounded-lg border border-[var(--app-border)] bg-[var(--app-panel-soft)] px-4 py-3">
            <span class="block text-xs font-extrabold uppercase tracking-wide text-[var(--app-muted)]">Tu usuario para entrar</span>
            <strong class="mt-1 block font-display text-xl font-black text-[var(--app-text)]">{{ $invitation->user->username }}</strong>
            <span class="mt-1 block text-xs font-semibold leading-5 text-[var(--app-muted)]">Anótalo: lo vas a necesitar junto con esta contraseña para iniciar sesión.</span>
        </div>

        @if ($errors->any())
            <div class="alert mb-5 border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('liga.invitation.accept', ['liga' => $liga, 'token' => $token]) }}" class="grid gap-5">
            @csrf

            <x-password-input name="password" label="Contraseña" autocomplete="new-password" :required="true" autofocus />

            <x-password-input name="password_confirmation" label="Repetir contraseña" autocomplete="new-password" :required="true" />

            <button type="submit" class="btn btn-primary w-full">Activar y entrar</button>
        </form>
    </section>
@endsection
