@extends('layouts.superadmin')

@section('title', 'Superadmin | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="surface-strong w-full max-w-md p-6 sm:p-8">
        <div class="mb-6">
            <span class="kicker">Control central</span>
            <h1 class="mt-3 font-display text-2xl font-black leading-tight text-[var(--app-text)] sm:text-3xl">Panel Superadmin</h1>
            <p class="mt-2 font-semibold leading-6 text-[var(--app-muted)]">Gestiona las ligas y carga los resultados oficiales.</p>
        </div>

        @if ($errors->any())
            <div class="alert mb-5 border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.login.store') }}" class="grid gap-5">
            @csrf

            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="email">
                Email
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="rounded-lg px-4 py-3.5 text-base" placeholder="superadmin@correo.com">
            </label>

            <x-password-input name="password" label="Contraseña" autocomplete="current-password" :required="true" />

            <button type="submit" class="btn btn-primary w-full">Entrar</button>
        </form>
    </section>
@endsection
