<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Liga;
use App\Models\Prediccion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LigaUserController extends Controller
{
    public function index(Liga $liga): View
    {
        // Auto-scopeado por LigaScope: solo los users de esta liga.
        $users = User::query()
            ->orderByRaw('CASE WHEN role = ? THEN 0 ELSE 1 END', [User::ROLE_LIGA_ADMIN])
            ->orderBy('name')
            ->get();

        return view('admin.users.index', [
            'liga' => $liga,
            'users' => $users,
        ]);
    }

    public function destroy(Liga $liga, User $user): RedirectResponse
    {
        // El binding {user} aplica el LigaScope: solo encuentra users de esta liga.
        if ($user->isLigaAdmin()) {
            return back()->with('security_alert', 'No se puede eliminar al administrador de la liga.');
        }

        DB::transaction(function () use ($user) {
            Prediccion::query()->where('usuario_id', $user->id)->delete();
            Invitation::query()->where('user_id', $user->id)->delete();
            $user->delete();
        });

        return back()->with('status', 'Usuario eliminado.');
    }
}
