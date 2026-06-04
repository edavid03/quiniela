@extends('layouts.app')

@section('title', 'Mi cuenta | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <span class="kicker">Cuenta</span>
            <h1 class="page-heading mt-3 md:text-5xl">Mi cuenta</h1>
            <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Actualiza tu nombre, tu usuario y tu contraseña.</p>
        </div>
        <div class="action-row">
            <a href="{{ route('liga.dashboard', ['liga' => $currentLiga]) }}" class="btn btn-secondary">Volver</a>
        </div>
    </section>

    @if (session('status'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 font-bold text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30">{{ session('status') }}</div>
    @endif

    <div class="grid gap-5 lg:grid-cols-2">
        {{-- Datos personales --}}
        <section class="surface-strong p-6 sm:p-8">
            <h2 class="font-display text-xl font-black text-[var(--app-text)]">Datos</h2>
            <p class="mt-1 text-sm font-semibold leading-6 text-[var(--app-muted)]">Tu nombre visible y el usuario con el que inicias sesion.</p>

            @if ($errors->updateProfile->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 font-bold text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">{{ $errors->updateProfile->first() }}</div>
            @endif

            <form method="POST" action="{{ route('liga.profile.update', ['liga' => $currentLiga]) }}" class="mt-5 grid gap-5">
                @csrf
                @method('PUT')

                <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="name">
                    Nombre
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="rounded-lg px-4 py-3" placeholder="Tu nombre">
                </label>

                <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="username">
                    Usuario
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required autocomplete="username" class="rounded-lg px-4 py-3" placeholder="tu_usuario">
                </label>

                <button type="submit" class="btn btn-primary w-full">Guardar datos</button>
            </form>
        </section>

        {{-- Contraseña --}}
        <section class="surface-strong p-6 sm:p-8">
            <h2 class="font-display text-xl font-black text-[var(--app-text)]">Contraseña</h2>
            <p class="mt-1 text-sm font-semibold leading-6 text-[var(--app-muted)]">Necesitas tu contraseña actual para cambiarla.</p>

            @if ($errors->updatePassword->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 font-bold text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">{{ $errors->updatePassword->first() }}</div>
            @endif

            <form method="POST" action="{{ route('liga.profile.password', ['liga' => $currentLiga]) }}" class="mt-5 grid gap-5">
                @csrf
                @method('PUT')

                <x-password-input name="current_password" label="Contraseña actual" autocomplete="current-password" :required="true" />

                <x-password-input name="password" label="Nueva contraseña" autocomplete="new-password" :required="true" />

                <x-password-input name="password_confirmation" label="Repetir nueva contraseña" autocomplete="new-password" :required="true" />

                <button type="submit" class="btn btn-primary w-full">Cambiar contraseña</button>
            </form>
        </section>
    </div>
@endsection
