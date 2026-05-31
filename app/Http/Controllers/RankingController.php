<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function index(): View
    {
        $rankings = User::query()
            ->leftJoin('predicciones', 'users.id', '=', 'predicciones.usuario_id')
            ->select([
                'users.id',
                'users.name',
                'users.username',
                DB::raw('COALESCE(SUM(predicciones.puntos), 0) as total_puntos'),
                DB::raw('COUNT(predicciones.id) as pronosticos'),
                DB::raw('COALESCE(SUM(CASE WHEN predicciones.puntos IS NOT NULL THEN 1 ELSE 0 END), 0) as evaluados'),
                DB::raw('COALESCE(SUM(CASE WHEN predicciones.acertado = 1 THEN 1 ELSE 0 END), 0) as exactos'),
                DB::raw('COALESCE(SUM(CASE WHEN predicciones.puntos = 1 THEN 1 ELSE 0 END), 0) as aciertos_signo'),
                DB::raw('COALESCE(SUM(CASE WHEN predicciones.puntos = 0 THEN 1 ELSE 0 END), 0) as fallos'),
            ])
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderByDesc('total_puntos')
            ->orderByDesc('exactos')
            ->orderByDesc('aciertos_signo')
            ->orderBy('fallos')
            ->orderByDesc('evaluados')
            ->orderBy('users.name')
            ->get();

        return view('rankings.index', [
            'rankings' => $rankings,
        ]);
    }
}
