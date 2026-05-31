@php
    $totalErrores = collect($rows)->filter(fn ($r) => ! empty($r['errors']))->count();
    $totalValidas = count($rows) - $totalErrores;
@endphp

@if (empty($rows))
    <div class="surface px-5 py-6 font-semibold text-[var(--app-muted)]">El archivo no tiene filas válidas. Revisa que tenga las columnas <code>email</code>, <code>username</code> y <code>name</code> en la primera fila.</div>
@else
    <div class="mb-4 flex flex-wrap items-center gap-2.5">
        @isset($usuariosDisponibles)
            <span class="inline-flex items-center gap-2 rounded-lg border border-[var(--app-border)] bg-[var(--app-panel-soft)] px-3 py-2 text-sm font-extrabold text-[var(--app-muted)]">
                Cupos disponibles: {{ $usuariosDisponibles === null ? 'sin limite' : $usuariosDisponibles }}
            </span>
        @endisset
        <span class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-extrabold text-[var(--app-success)] dark:border-emerald-900/50 dark:bg-emerald-950/30">
            <x-icon name="check" class="h-4 w-4" /> {{ $totalValidas }} {{ $totalValidas === 1 ? 'fila válida' : 'filas válidas' }}
        </span>
        @if ($totalErrores > 0)
            <span class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-extrabold text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">
                {{ $totalErrores }} con {{ $totalErrores === 1 ? 'error' : 'errores' }} · corrígelas o quítalas
            </span>
        @endif
    </div>

    <form method="POST" action="{{ route('liga.admin.import.accept', ['liga' => $liga]) }}">
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
            <a href="{{ route('liga.admin.users.index', ['liga' => $liga]) }}" class="btn btn-secondary">Volver</a>
            <button type="submit" class="btn btn-primary">Confirmar y enviar invitaciones</button>
        </div>
    </form>
@endif
