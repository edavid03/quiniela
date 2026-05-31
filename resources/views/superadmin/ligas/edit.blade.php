@extends('layouts.superadmin')

@section('title', 'Editar liga | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6">
        <span class="kicker">Superadmin</span>
        <h1 class="page-heading mt-3">Editar liga</h1>
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

    <form method="POST" action="{{ route('superadmin.ligas.update', $liga) }}" class="grid max-w-2xl gap-6">
        @csrf
        @method('PUT')

        <section class="surface grid gap-5 p-5">
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="name">
                Nombre
                <input id="name" name="name" type="text" value="{{ old('name', $liga->name) }}" required class="rounded-lg px-4 py-3">
            </label>
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="slug">
                Slug (URL)
                <input id="slug" name="slug" type="text" value="{{ old('slug', $liga->slug) }}" required class="rounded-lg px-4 py-3" pattern="[a-z0-9-]+">
            </label>
            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="plan_id">
                Plan
                <select id="plan_id" name="plan_id" required class="rounded-lg px-4 py-3">
                    @foreach ($planes as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id', $liga->plan_id) === $plan->id)>
                            Plan {{ $plan->id }} - {{ $plan->name }} ({{ $plan->limite_usuarios === null ? 'sin limite' : $plan->limite_usuarios.' usuarios' }})
                        </option>
                    @endforeach
                </select>
                <span class="text-xs font-semibold text-[var(--app-muted)]">Usuarios actuales: {{ $liga->usuariosActuales() }}. El administrador cuenta dentro del limite.</span>
            </label>
            <label class="flex items-center gap-3 text-sm font-extrabold text-[var(--app-text)]">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $liga->is_active)) class="h-5 w-5 accent-[var(--fwc-red)]">
                Liga activa
            </label>
        </section>

        <div class="action-row">
            <a href="{{ route('superadmin.ligas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
@endsection
