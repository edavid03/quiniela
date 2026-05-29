@extends('layouts.app')

@section('title', 'Usuarios | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <span class="kicker">Administración · {{ $liga->name }}</span>
            <h1 class="page-heading mt-3 md:text-5xl">Usuarios de la liga</h1>
            <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Importa usuarios desde un Excel y se les envía una invitación por correo para activar su cuenta.</p>
        </div>
        <div class="action-row sm:w-fit">
            <a href="{{ route('liga.admin.import.template', ['liga' => $currentLiga]) }}" class="btn btn-secondary">Descargar plantilla</a>
            <a href="{{ route('liga.admin.import.create', ['liga' => $currentLiga]) }}" class="btn btn-primary">Importar Excel</a>
        </div>
    </section>

    @if (session('status'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 font-bold text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30">{{ session('status') }}</div>
    @endif
    @if (session('security_alert'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 font-bold text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">{{ session('security_alert') }}</div>
    @endif

    <section class="surface overflow-hidden">
        <div class="grid grid-cols-[1fr_6.5rem_8rem] items-center gap-3 bg-[var(--app-panel-soft)] px-5 py-3 text-sm font-extrabold text-[var(--app-muted)]">
            <div>Usuario</div>
            <div class="text-center">Estado</div>
            <div class="text-center">Acciones</div>
        </div>

        @forelse ($users as $user)
            <article class="grid grid-cols-[1fr_6.5rem_8rem] items-center gap-3 border-t border-[var(--app-border)] px-5 py-4">
                <div class="min-w-0">
                    <strong class="block truncate">{{ $user->name }}</strong>
                    <span class="block truncate text-sm text-[var(--app-muted)]">{{ $user->username }} · {{ $user->email }} @if ($user->isLigaAdmin()) · <span class="font-black text-[var(--app-primary)]">admin</span> @endif</span>
                </div>

                <div class="flex justify-center">
                    @if ($user->email_verified_at)
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-black text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30">Activo</span>
                    @else
                        <span class="rounded-full border border-[var(--app-border)] bg-[var(--app-panel-soft)] px-2.5 py-1 text-xs font-black text-[var(--app-secondary)]">Pendiente</span>
                    @endif
                </div>

                <div class="flex justify-center">
                    @if ($user->isLigaAdmin())
                        <button type="button" disabled title="No se puede eliminar al administrador de la liga" class="btn btn-secondary w-full cursor-not-allowed opacity-45">Eliminar</button>
                    @else
                        <form method="POST" action="{{ route('liga.admin.users.destroy', ['liga' => $currentLiga, 'user' => $user]) }}" class="w-full" onsubmit="return confirm('¿Eliminar a {{ $user->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-secondary w-full text-[var(--app-danger)]">Eliminar</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="px-5 py-6 font-semibold text-[var(--app-muted)]">Todavía no hay usuarios. Importa el primer lote.</div>
        @endforelse
    </section>
@endsection
