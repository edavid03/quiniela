<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use App\Models\Prediccion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ResultadoController extends Controller
{
    public function index(Request $request): View
    {
        $partidos = Partido::query()
            ->with(['local', 'visitante'])
            ->orderBy('fecha_utc')
            ->get();

        $ahora = now('America/Caracas');

        $porSeccion = $partidos->groupBy(function (Partido $partido) use ($ahora) {
            $fecha = Carbon::parse($partido->fecha_caracas, 'America/Caracas');

            if ($fecha->isSameDay($ahora)) {
                return 'hoy';
            }

            return $fecha->isAfter($ahora) ? 'proximos' : 'jugados';
        });

        $hoy = ($porSeccion->get('hoy') ?? collect())->sortBy('fecha_utc')->values();
        $proximos = ($porSeccion->get('proximos') ?? collect())->sortBy('fecha_utc')->values();
        $jugados = ($porSeccion->get('jugados') ?? collect())->sortByDesc('fecha_utc')->values();

        return view('resultados.index', [
            'gruposHoy' => $this->agruparPorFase($hoy),
            'gruposProximos' => $this->agruparPorDia($proximos, $ahora),
            'gruposJugados' => $this->agruparPorFase($jugados),
            'totalPartidos' => $partidos->count(),
            'predicciones' => Prediccion::query()
                ->where('usuario_id', $request->user()->id)
                ->get()
                ->keyBy('partido_id'),
        ]);
    }

    private function agruparPorFase(Collection $partidos): Collection
    {
        return $partidos->groupBy('fase')
            ->map(fn (Collection $grupo, string $fase) => ['titulo' => $fase, 'partidos' => $grupo]);
    }

    private function agruparPorDia(Collection $partidos, Carbon $ahora): Collection
    {
        return $partidos
            ->groupBy(fn (Partido $p) => Carbon::parse($p->fecha_caracas, 'America/Caracas')->format('Y-m-d'))
            ->map(function (Collection $grupo) use ($ahora) {
                $fecha = Carbon::parse($grupo->first()->fecha_caracas, 'America/Caracas');

                $titulo = $fecha->isSameDay($ahora->copy()->addDay())
                    ? 'Mañana'
                    : ucfirst($fecha->locale('es')->translatedFormat('l j \d\e F'));

                return ['titulo' => $titulo, 'partidos' => $grupo];
            });
    }
}
