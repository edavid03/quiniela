@extends('layouts.app')

@section('title', 'Nueva clave | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="surface-strong w-full max-w-lg p-5 sm:p-8">
        <div class="mb-8">
            <h1 class="font-display text-3xl font-black text-[var(--app-text)]">Crea una nueva clave</h1>
            <p class="mt-3 font-semibold leading-6 text-[var(--app-muted)]">Est&aacute;s cambiando la contrase&ntilde;a del usuario <strong>{{ $username }}</strong> en <strong>{{ $liga->name }}</strong>.</p>
        </div>

        @if ($errors->any())
            <div class="alert border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('liga.password.update', ['liga' => $liga]) }}" class="grid gap-5">
            @csrf

            <input type="hidden" name="username" value="{{ $username }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="password">
                Nueva contrase&ntilde;a
                <input id="password" name="password" type="password" required autofocus autocomplete="new-password" class="rounded-lg px-4 py-3.5 text-base" placeholder="••••••••">
            </label>

            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="password_confirmation">
                Repetir contrase&ntilde;a
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="rounded-lg px-4 py-3.5 text-base" placeholder="••••••••">
            </label>

            <button type="submit" class="btn btn-primary w-full">Actualizar clave</button>
        </form>
    </section>
@endsection
