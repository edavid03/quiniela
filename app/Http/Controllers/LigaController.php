<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Liga;
use App\Models\Prediccion;
use App\Models\Scopes\LigaScope;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LigaController extends Controller
{
    public function index(): View
    {
        return view('superadmin.ligas.index', [
            'ligas' => Liga::query()
                ->withCount('users')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('superadmin.ligas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLiga($request);

        DB::transaction(function () use ($data) {
            $liga = Liga::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'is_active' => true,
            ]);

            // Cada liga nace con su admin; despues el admin importa a sus usuarios.
            User::create([
                'liga_id' => $liga->id,
                'name' => $data['admin_name'],
                'username' => $data['admin_username'],
                'email' => $data['admin_email'],
                'password' => $data['admin_password'],
                'role' => User::ROLE_LIGA_ADMIN,
            ]);
        });

        return redirect()
            ->route('superadmin.ligas.index')
            ->with('status', 'Liga creada correctamente.');
    }

    public function edit(Liga $liga): View
    {
        return view('superadmin.ligas.edit', ['liga' => $liga]);
    }

    public function update(Request $request, Liga $liga): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'lowercase', 'regex:/^[a-z0-9-]+$/',
                Rule::notIn(Liga::RESERVED_SLUGS),
                Rule::unique('ligas', 'slug')->ignore($liga->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        $liga->update($data);

        return redirect()
            ->route('superadmin.ligas.index')
            ->with('status', 'Liga actualizada.');
    }

    public function destroy(Liga $liga): RedirectResponse
    {
        // Borrado app-level: el FK restrict de predicciones.usuario_id obliga a
        // limpiar en orden (predicciones -> invitaciones -> users -> liga).
        DB::transaction(function () use ($liga) {
            Prediccion::withoutGlobalScope(LigaScope::class)
                ->where('liga_id', $liga->id)
                ->delete();

            Invitation::where('liga_id', $liga->id)->delete();

            User::withoutGlobalScope(LigaScope::class)
                ->where('liga_id', $liga->id)
                ->delete();

            $liga->delete();
        });

        return redirect()
            ->route('superadmin.ligas.index')
            ->with('status', 'Liga eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLiga(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'lowercase', 'regex:/^[a-z0-9-]+$/',
                Rule::notIn(Liga::RESERVED_SLUGS),
                Rule::unique('ligas', 'slug'),
            ],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_username' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);
    }
}
