@extends('layouts.app')

@section('title', 'Importar usuarios | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6">
        <span class="kicker">Administración · {{ $liga->name }}</span>
        <h1 class="page-heading mt-3">Importar usuarios</h1>
        <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Subí un archivo <strong>.xlsx</strong> con las columnas <code>email</code>, <code>username</code> y <code>name</code> en la primera fila. Vas a poder revisar y editar antes de enviar las invitaciones.</p>
    </section>

    @if ($errors->any())
        <div class="alert mb-5 border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('liga.admin.import.preview', ['liga' => $currentLiga]) }}" enctype="multipart/form-data" class="surface grid max-w-xl gap-5 p-5">
        @csrf
        <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="file">
            Archivo Excel
            <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" required class="rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] px-4 py-3">
        </label>
        <div class="action-row">
            <a href="{{ route('liga.admin.users.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary">Volver</a>
            <button type="submit" class="btn btn-primary">Previsualizar</button>
        </div>
    </form>
@endsection
