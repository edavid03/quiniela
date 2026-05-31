@extends('layouts.superadmin')

@section('title', 'Ligas | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <span class="kicker">Superadmin</span>
            <h1 class="page-heading mt-3 md:text-5xl">Ligas</h1>
            <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Cada liga tiene su propio admin, sus usuarios y su ranking. Los partidos y resultados son compartidos por todas.</p>
        </div>
        <a href="{{ route('superadmin.ligas.create') }}" class="btn btn-primary sm:w-fit">Nueva liga</a>
    </section>

    @if (session('status'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 font-bold text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30">{{ session('status') }}</div>
    @endif

    <section class="surface overflow-hidden">
        <div class="grid grid-cols-[1fr_auto_auto_auto] gap-3 bg-[var(--app-panel-soft)] px-5 py-3 text-sm font-extrabold text-[var(--app-muted)]">
            <div>Liga</div>
            <div class="text-right">Plan</div>
            <div class="text-right">Usuarios</div>
            <div class="text-right">Acciones</div>
        </div>

        @forelse ($ligas as $liga)
            <article class="grid grid-cols-[1fr_auto_auto_auto] items-center gap-3 border-t border-[var(--app-border)] px-5 py-4">
                <div class="min-w-0">
                    <strong class="block truncate">{{ $liga->name }}</strong>
                    <span class="text-sm text-[var(--app-muted)]">/{{ $liga->slug }} @unless ($liga->is_active) · <span class="text-[var(--app-danger)]">inactiva</span> @endunless</span>
                </div>
                <div class="text-right font-extrabold">Plan {{ $liga->plan_id }} - {{ $liga->plan?->name }}</div>
                <div class="text-right font-extrabold">{{ $liga->users_count }} / {{ $liga->plan?->limite_usuarios ?? 'Sin limite' }}</div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('superadmin.ligas.edit', $liga) }}" class="btn btn-secondary">Editar</a>
                    <form method="POST" action="{{ route('superadmin.ligas.destroy', $liga) }}" onsubmit="return confirm('¿Eliminar la liga {{ $liga->name }} y todos sus usuarios?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary text-[var(--app-danger)]">Eliminar</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="px-5 py-6 font-semibold text-[var(--app-muted)]">Todavía no hay ligas. Crea la primera.</div>
        @endforelse
    </section>
@endsection
