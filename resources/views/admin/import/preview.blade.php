@extends('layouts.app')

@section('title', 'Previsualizar importación | '.config('app.name', 'Quiniela'))

@section('content')
    <section class="mb-6">
        <span class="kicker">Administración · {{ $liga->name }}</span>
        <h1 class="page-heading mt-3">Previsualización</h1>
        <p class="mt-2 max-w-2xl leading-7 text-[var(--app-muted)]">Revisa y corrige los datos. Las filas con errores en rojo se deben arreglar o quitar antes de enviar. Al confirmar, cada usuario recibe un correo con su invitación.</p>
    </section>

    @include('admin.import._rows', ['liga' => $liga, 'rows' => $rows])

    <script>
        document.querySelectorAll('[data-remove-row]').forEach((btn) => {
            btn.addEventListener('click', () => btn.closest('[data-import-row]').remove());
        });
    </script>
@endsection
