@extends('layouts.app')

@section('title', 'Ranking | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="page-heading">Ranking</h1>
            <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Tabla de posiciones del grupo seg&uacute;n los puntos acumulados por los pron&oacute;sticos evaluados.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary sm:w-fit">Volver</a>
    </section>

    <section class="surface overflow-hidden">
        <div class="grid grid-cols-[3rem_1fr_4.5rem] gap-3 bg-[var(--app-panel-soft)] px-3 py-3 text-sm font-extrabold text-[var(--app-muted)] sm:grid-cols-[4rem_1fr_5rem] sm:px-5 md:grid-cols-[4rem_1fr_repeat(4,minmax(5.5rem,auto))]">
            <div>#</div>
            <div>Usuario</div>
            <div class="text-right">Puntos</div>
            <div class="hidden text-right md:block">Exactos</div>
            <div class="hidden text-right md:block">Evaluados</div>
            <div class="hidden text-right md:block">Pron&oacute;sticos</div>
        </div>

        @forelse ($rankings as $index => $ranking)
            <article class="grid grid-cols-[3rem_1fr_4.5rem] gap-3 border-t border-[var(--app-border)] px-3 py-4 sm:grid-cols-[4rem_1fr_5rem] sm:px-5 md:grid-cols-[4rem_1fr_repeat(4,minmax(5.5rem,auto))]">
                <div>
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-[var(--app-primary)] font-extrabold text-white dark:text-[var(--app-bg)]">{{ $index + 1 }}</span>
                </div>
                <div class="min-w-0">
                    <strong class="block truncate">{{ $ranking->name }}</strong>
                    <span class="text-sm text-[var(--app-muted)]">{{ $ranking->username }}</span>
                </div>
                <div class="text-right font-extrabold">{{ (int) $ranking->total_puntos }}</div>
                <div class="hidden text-right font-bold text-[var(--app-muted)] md:block">{{ (int) $ranking->exactos }}</div>
                <div class="hidden text-right font-bold text-[var(--app-muted)] md:block">{{ (int) $ranking->evaluados }}</div>
                <div class="hidden text-right font-bold text-[var(--app-muted)] md:block">{{ (int) $ranking->pronosticos }}</div>
            </article>
        @empty
            <div class="px-5 py-6 text-[var(--app-muted)]">Todav&iacute;a no hay usuarios para mostrar.</div>
        @endforelse
    </section>
@endsection
