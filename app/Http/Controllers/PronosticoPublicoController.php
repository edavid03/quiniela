<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PronosticoPublicoController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->where('role', User::ROLE_LIGA_USER)
            ->orderBy('name')
            ->get();

        $partidos = Partido::query()
            ->whereNotNull('goles_local')
            ->whereNotNull('goles_visitante')
            ->with(['local', 'visitante', 'predicciones' => function ($q) {
                $q->with('usuario');
            }])
            ->orderBy('fecha_utc', 'desc')
            ->get();

        return view('pronosticos-publicos.index', [
            'partidos' => $partidos,
            'users' => $users,
        ]);
    }
}
