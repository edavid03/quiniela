@extends('layouts.app')

@section('title', 'Recuperar clave | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="surface-strong w-full max-w-lg p-5 sm:p-8">
        <div class="mb-8">
            <h1 class="font-display text-3xl font-black text-[var(--app-text)]">Recupera tu clave</h1>
            <p class="mt-3 font-semibold leading-6 text-[var(--app-muted)]">Escribe tu usuario de la liga <strong>{{ $liga->name }}</strong>. Si existe, enviaremos un enlace de recuperaci&oacute;n al correo registrado.</p>
        </div>

        @if (session('status'))
            <div class="alert border-emerald-200 bg-emerald-50 text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30">
                {{ session('status') }}
            </div>
        @endif

        @if (session('security_alert'))
            <div class="alert border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
                {{ session('security_alert') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('liga.password.email', ['liga' => $liga]) }}" class="grid gap-5">
            @csrf

            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="username">
                Usuario
                <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus autocomplete="username" class="rounded-lg px-4 py-3.5 text-base" placeholder="tu_usuario">
            </label>

            <button type="submit" class="btn btn-primary w-full">Enviar enlace</button>
            <a href="{{ route('liga.login', ['liga' => $liga]) }}" class="btn btn-secondary w-full">Volver al login</a>
        </form>
    </section>
@endsection
