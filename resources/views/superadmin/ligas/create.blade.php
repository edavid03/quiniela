@extends('layouts.superadmin')

@section('title', 'Nueva liga | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6">
        <span class="kicker">Superadmin</span>
        <h1 class="page-heading mt-3">Nueva liga</h1>
        <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">El slug vive en la raíz de la URL: <code>/{slug}/login</code>. Se crea junto con su administrador.</p>
    </section>

    @if ($errors->any())
        <div class="alert mb-5 border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.ligas.store') }}" class="grid max-w-2xl gap-6">
        @csrf

        <section class="surface grid gap-5 p-5">
            <h2 class="font-display text-lg font-black">Datos de la liga</h2>
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="name">
                Nombre
                <input id="name" name="name" type="text" value="{{ old('name') }}" required class="rounded-lg px-4 py-3" placeholder="Liga ADN">
            </label>
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="slug">
                Slug (URL)
                <input id="slug" name="slug" type="text" value="{{ old('slug') }}" required class="rounded-lg px-4 py-3" placeholder="liga-adn" pattern="[a-z0-9-]+">
                <span class="text-xs font-semibold text-[var(--app-muted)]">Solo minúsculas, números y guiones.</span>
            </label>
        </section>

        <section class="surface grid gap-5 p-5">
            <h2 class="font-display text-lg font-black">Administrador de la liga</h2>
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="admin_name">
                Nombre
                <input id="admin_name" name="admin_name" type="text" value="{{ old('admin_name') }}" required class="rounded-lg px-4 py-3">
            </label>
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="admin_username">
                Usuario
                <input id="admin_username" name="admin_username" type="text" value="{{ old('admin_username') }}" required class="rounded-lg px-4 py-3">
            </label>
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="admin_email">
                Email
                <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email') }}" required class="rounded-lg px-4 py-3">
            </label>
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="admin_password">
                Contraseña
                <input id="admin_password" name="admin_password" type="password" required class="rounded-lg px-4 py-3" placeholder="mínimo 8 caracteres">
            </label>
        </section>

        <div class="action-row">
            <a href="{{ route('superadmin.ligas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Crear liga</button>
        </div>
    </form>
@endsection
