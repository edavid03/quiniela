@extends('layouts.app')

@section('title', 'Agregar usuarios | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <span class="kicker">Administracion &middot; {{ $liga->name }}</span>
            <h1 class="page-heading mt-3">Agregar usuarios</h1>
            <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Crea un usuario manualmente y envia su invitacion. Si necesitas cargar varios, puedes apoyarte con un archivo Excel o CSV.</p>
            <p class="mt-2 text-sm font-extrabold text-[var(--app-secondary)]">
                Cupos disponibles: {{ $usuariosDisponibles === null ? 'sin limite' : $usuariosDisponibles }}.
            </p>
        </div>
        <a href="{{ route('liga.admin.users.index', ['liga' => $currentLiga]) }}" class="btn btn-secondary sm:w-fit">Volver</a>
    </section>

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 font-bold text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30">{{ $errors->first() }}</div>
    @endif

    <section class="surface-strong p-5 sm:p-6">
        <div class="mb-5">
            <h2 class="font-display text-2xl font-black text-[var(--app-text)]">Agregar usuario manualmente</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-[var(--app-muted)]">El usuario recibira un correo para activar su cuenta y elegir su contrasena.</p>
        </div>

        <form method="POST" action="{{ route('liga.admin.import.accept', ['liga' => $currentLiga]) }}" class="grid gap-4 md:grid-cols-3">
            @csrf

            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="manual-name">
                Nombre
                <input id="manual-name" name="rows[0][name]" type="text" value="{{ old('rows.0.name') }}" required class="rounded-lg px-4 py-3 text-base" placeholder="Ana Perez">
            </label>

            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="manual-username">
                Usuario
                <input id="manual-username" name="rows[0][username]" type="text" value="{{ old('rows.0.username') }}" required class="rounded-lg px-4 py-3 text-base" placeholder="ana">
            </label>

            <label class="grid gap-2 text-sm font-extrabold text-[var(--app-text)]" for="manual-email">
                Email
                <input id="manual-email" name="rows[0][email]" type="email" value="{{ old('rows.0.email') }}" required class="rounded-lg px-4 py-3 text-base" placeholder="ana@correo.com">
            </label>

            <div class="action-row md:col-span-3">
                <button type="submit" class="btn btn-primary">Enviar invitacion</button>
            </div>
        </form>
    </section>

    <section class="mt-6 surface p-5 sm:p-6">
        <div class="mb-5">
            <h2 class="font-display text-xl font-black text-[var(--app-text)]">Agregar varios con archivo</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-[var(--app-muted)]">Sube un archivo <strong>.xlsx</strong>, <strong>.xls</strong> o <strong>.csv</strong> con columnas <code>email</code>, <code>username</code> y <code>name</code>. Podras revisar y editar la vista previa antes de confirmar.</p>
        </div>

        <form
            method="POST"
            action="{{ route('liga.admin.import.preview', ['liga' => $currentLiga]) }}"
            enctype="multipart/form-data"
            data-import-form
        >
            @csrf

            <label
                for="import-file"
                data-dropzone
                class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed border-[var(--app-border)] px-6 py-10 text-center transition-colors duration-200 hover:border-[var(--app-primary)] hover:bg-[var(--app-panel-soft)]"
            >
                <span class="grid h-14 w-14 place-items-center rounded-xl bg-[var(--app-panel-soft)] text-[var(--app-primary)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-7 w-7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5m0 0L7.5 9m4.5-4.5L16.5 9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15v3a1.5 1.5 0 0 0 1.5 1.5h12a1.5 1.5 0 0 0 1.5-1.5v-3" />
                    </svg>
                </span>
                <span class="font-display text-base font-black text-[var(--app-text)]">
                    Arrastra tu archivo o haz clic para elegirlo
                </span>
                <span class="text-sm font-semibold text-[var(--app-muted)]" data-file-name>Formatos: .xlsx, .xls o .csv &middot; hasta 2 MB</span>
            </label>

            <input id="import-file" name="file" type="file" accept=".xlsx,.xls,.csv" required class="hidden">

            <div class="action-row mt-5" data-fallback>
                <button type="submit" class="btn btn-secondary">Previsualizar archivo</button>
            </div>
        </form>
    </section>

    <div id="preview-results" class="mt-6"></div>

    <script>
        (() => {
            const form = document.querySelector('[data-import-form]');
            if (! form) {
                return;
            }

            const input = form.querySelector('input[type="file"]');
            const dropzone = form.querySelector('[data-dropzone]');
            const fallback = form.querySelector('[data-fallback]');
            const fileName = form.querySelector('[data-file-name]');
            const results = document.querySelector('#preview-results');
            const token = form.querySelector('input[name="_token"]').value;
            const url = form.getAttribute('action');

            if (fallback) {
                fallback.querySelector('button[type="submit"]')?.remove();
            }

            const activeClasses = ['border-[var(--app-primary)]', 'bg-[var(--app-panel-soft)]'];

            const setLoading = () => {
                results.innerHTML =
                    '<div class="surface flex items-center gap-3 px-5 py-6 font-semibold text-[var(--app-muted)]">' +
                    '<span class="h-4 w-4 animate-spin rounded-full border-2 border-[var(--app-border)] border-t-[var(--app-primary)]"></span>' +
                    'Procesando archivo...</div>';
            };

            const showError = (msg) => {
                results.innerHTML =
                    '<div class="alert border-red-200 bg-red-50 text-[var(--app-danger)] dark:border-red-900/50 dark:bg-red-950/30"></div>';
                results.firstElementChild.textContent = msg;
            };

            const preview = async (file) => {
                if (! file) {
                    return;
                }

                if (fileName) {
                    fileName.textContent = file.name;
                }

                setLoading();

                const body = new FormData();
                body.append('file', file);
                body.append('_token', token);

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                        body,
                    });

                    if (! res.ok) {
                        let msg = 'No se pudo procesar el archivo. Reintenta.';

                        if (res.status === 422) {
                            const data = await res.json().catch(() => null);
                            msg = data?.errors?.file?.[0] ?? data?.message ?? msg;
                        }

                        showError(msg);

                        return;
                    }

                    results.innerHTML = await res.text();
                    results.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } catch (e) {
                    showError('No se pudo conectar con el servidor. Reintenta.');
                }
            };

            input.addEventListener('change', () => preview(input.files[0]));

            ['dragenter', 'dragover'].forEach((ev) => {
                dropzone.addEventListener(ev, (e) => {
                    e.preventDefault();
                    dropzone.classList.add(...activeClasses);
                });
            });

            ['dragleave', 'drop'].forEach((ev) => {
                dropzone.addEventListener(ev, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove(...activeClasses);
                });
            });

            dropzone.addEventListener('drop', (e) => {
                const file = e.dataTransfer?.files?.[0];

                if (file) {
                    input.files = e.dataTransfer.files;
                    preview(file);
                }
            });

            results.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove-row]');

                if (btn) {
                    btn.closest('[data-import-row]')?.remove();
                }
            });
        })();
    </script>
@endsection
