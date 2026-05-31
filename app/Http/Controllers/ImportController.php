<?php

namespace App\Http\Controllers;

use App\Exports\UsersTemplateExport;
use App\Imports\UsersImport;
use App\Mail\LigaInvitationMail;
use App\Models\Invitation;
use App\Models\Liga;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportController extends Controller
{
    public function create(Liga $liga): View
    {
        return view('admin.import.create', [
            'liga' => $liga,
            'usuariosDisponibles' => $liga->usuariosDisponibles(),
        ]);
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new UsersTemplateExport, 'plantilla-usuarios.xlsx');
    }

    public function preview(Request $request, Liga $liga): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        $raw = Excel::toArray(new UsersImport, $request->file('file'))[0] ?? [];

        $rows = [];
        $seenEmail = [];
        $seenUsername = [];
        $usuariosDisponibles = $liga->usuariosDisponibles();
        $usuariosNuevosValidos = 0;

        foreach ($raw as $r) {
            $email = trim((string) ($r['email'] ?? ''));
            $username = trim((string) ($r['username'] ?? ''));
            $name = trim((string) ($r['name'] ?? ''));

            if ($email === '' && $username === '' && $name === '') {
                continue;
            }

            $errors = [];

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email inválido';
            }
            if ($username === '') {
                $errors[] = 'Falta username';
            }
            if ($name === '') {
                $errors[] = 'Falta nombre';
            }

            $lowEmail = mb_strtolower($email);
            $lowUsername = mb_strtolower($username);

            if ($lowEmail !== '' && isset($seenEmail[$lowEmail])) {
                $errors[] = 'Email duplicado en el archivo';
            }
            if ($lowUsername !== '' && isset($seenUsername[$lowUsername])) {
                $errors[] = 'Username duplicado en el archivo';
            }
            $seenEmail[$lowEmail] = true;
            $seenUsername[$lowUsername] = true;

            // Dups contra usuarios ya existentes en ESTA liga (auto-scopeado).
            if ($email !== '' && User::query()->where('email', $email)->exists()) {
                $errors[] = 'Email ya existe en la liga';
            }
            if ($username !== '' && User::query()->where('username', $username)->exists()) {
                $errors[] = 'Username ya existe en la liga';
            }

            if ($errors === []) {
                if ($usuariosDisponibles !== null && $usuariosNuevosValidos >= $usuariosDisponibles) {
                    $errors[] = 'Excede el limite de usuarios del plan';
                } else {
                    $usuariosNuevosValidos++;
                }
            }

            $rows[] = [
                'email' => $email,
                'username' => $username,
                'name' => $name,
                'errors' => $errors,
            ];
        }

        // En AJAX devolvemos solo el fragmento de la tabla (preview en vivo en la
        // misma pantalla); sin JS, la pagina completa de preview como fallback.
        $view = $request->ajax() ? 'admin.import._rows' : 'admin.import.preview';

        return view($view, [
            'liga' => $liga,
            'rows' => $rows,
            'usuariosDisponibles' => $usuariosDisponibles,
        ]);
    }

    public function accept(Request $request, Liga $liga): RedirectResponse
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.email' => ['required', 'email'],
            'rows.*.username' => ['required', 'string', 'max:255'],
            'rows.*.name' => ['required', 'string', 'max:255'],
        ]);

        $created = 0;
        $skipped = 0;
        $usuariosNuevos = 0;

        foreach ($validated['rows'] as $row) {
            $email = trim($row['email']);
            $username = trim($row['username']);

            $exists = User::query()
                ->where(fn ($q) => $q->where('email', $email)->orWhere('username', $username))
                ->exists();

            if (! $exists) {
                $usuariosNuevos++;
            }
        }

        if (! $liga->permiteAgregarUsuarios($usuariosNuevos)) {
            return back()
                ->withErrors(['rows' => 'La importacion excede el limite de usuarios del plan de la liga.'])
                ->with('security_alert', 'La importacion excede el limite de usuarios del plan de la liga.')
                ->withInput();
        }

        foreach ($validated['rows'] as $row) {
            $email = trim($row['email']);
            $username = trim($row['username']);
            $name = trim($row['name']);

            $exists = User::query()
                ->where(fn ($q) => $q->where('email', $email)->orWhere('username', $username))
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($liga, $email, $username, $name) {
                $user = User::create([
                    'liga_id' => $liga->id,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    // Placeholder: el cast 'hashed' lo hashea. El usuario define
                    // su contraseña real al aceptar la invitación.
                    'password' => Str::random(40),
                    'role' => User::ROLE_LIGA_USER,
                ]);

                [, $rawToken] = Invitation::generate($user);

                $url = route('liga.invitation.show', ['liga' => $liga, 'token' => $rawToken]);

                Mail::to($user->email)->queue(new LigaInvitationMail($liga, $user->username, $url));
            });

            $created++;
        }

        return redirect()
            ->route('liga.admin.users.index', ['liga' => $liga])
            ->with('status', "Se invitaron {$created} usuarios. ({$skipped} omitidos por duplicado)");
    }
}
