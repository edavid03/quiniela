@extends('layouts.app')

@section('title', 'Previsualizar importación | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6">
        <span class="kicker">Administración · {{ $liga->name }}</span>
        <h1 class="page-heading mt-3">Previsualización</h1>
        <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Revisa y corrige los datos. Las filas con errores en rojo se deben arreglar o quitar antes de enviar. Al confirmar, cada usuario recibe un correo con su invitación.</p>
    </section>

    @php $totalErrores = collect($rows)->filter(fn ($r) => ! empty($r['errors']))->count(); @endphp

    @if ($totalErrores > 0)
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 font-bold text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
            Hay {{ $totalErrores }} fila(s) con problemas. Corrígelas o quítalas antes de confirmar.
        </div>
    @endif

    @if (empty($rows))
        <div class="surface px-5 py-6 font-semibold text-[var(--app-muted)]">El archivo no tiene filas válidas.</div>
    @else
        <form method="POST" action="{{ route('liga.admin.import.accept', ['liga' => $currentLiga]) }}">
            @csrf

            <section class="surface overflow-x-auto">
                <table class="w-full min-w-[40rem] text-sm">
                    <thead>
                        <tr class="bg-[var(--app-panel-soft)] text-left font-extrabold text-[var(--app-muted)]">
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr class="border-t border-[var(--app-border)] {{ empty($row['errors']) ? '' : 'bg-red-50 dark:bg-red-950/20' }}" data-import-row>
                                <td class="px-4 py-2">
                                    <input name="rows[{{ $i }}][name]" type="text" value="{{ $row['name'] }}" class="w-full rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] px-3 py-2">
                                </td>
                                <td class="px-4 py-2">
                                    <input name="rows[{{ $i }}][username]" type="text" value="{{ $row['username'] }}" class="w-full rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] px-3 py-2">
                                </td>
                                <td class="px-4 py-2">
                                    <input name="rows[{{ $i }}][email]" type="email" value="{{ $row['email'] }}" class="w-full rounded-lg border border-[var(--app-border)] bg-[var(--app-panel)] px-3 py-2">
                                </td>
                                <td class="px-4 py-2 text-xs font-bold">
                                    @if (empty($row['errors']))
                                        <span class="text-[var(--app-success)]">OK</span>
                                    @else
                                        <span class="text-[var(--app-danger)]">{{ implode(', ', $row['errors']) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" class="btn btn-secondary px-3 py-1.5 text-xs" data-remove-row>Quitar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <div class="action-row mt-5">
                <a href="{{ route('liga.admin.import.create', ['liga' => $currentLiga]) }}" class="btn btn-secondary">Cargar otro archivo</a>
                <button type="submit" class="btn btn-primary">Confirmar y enviar invitaciones</button>
            </div>
        </form>

        <script>
            document.querySelectorAll('[data-remove-row]').forEach((btn) => {
                btn.addEventListener('click', () => btn.closest('[data-import-row]').remove());
            });
        </script>
    @endif
@endsection
