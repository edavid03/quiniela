<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminPartidoResultadoController extends Controller
{
    public function edit(): View
    {
        $ultimaSync = Cache::store('database')->get('partidos_sync_last_run');

        return view('admin.resultados', [
            'partidos' => Partido::query()
                ->with(['local', 'visitante'])
                ->orderBy('fecha_utc')
                ->get(),
            'ultimaSync' => $ultimaSync ? Carbon::parse($ultimaSync) : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'resultados' => ['required', 'array'],
            'resultados.*.goles_local' => ['nullable', 'integer', 'min:0', 'max:99'],
            'resultados.*.goles_visitante' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $resultados = $validated['resultados'];
        $partidoIds = array_map('intval', array_keys($resultados));
        $partidos = Partido::query()
            ->whereIn('id', $partidoIds)
            ->get()
            ->keyBy('id');

        if ($partidos->count() !== count($partidoIds)) {
            return back()
                ->withErrors(['resultados' => 'Uno de los partidos seleccionados no existe.'])
                ->with('security_alert', 'Se detecto un partido invalido en el formulario.')
                ->withInput();
        }

        foreach ($resultados as $partidoId => $resultado) {
            $golesLocal = $resultado['goles_local'] ?? null;
            $golesVisitante = $resultado['goles_visitante'] ?? null;

            if ($golesLocal === null && $golesVisitante === null) {
                continue;
            }

            if ($golesLocal === null || $golesVisitante === null) {
                return back()
                    ->withErrors(['resultados' => 'Cada resultado debe tener goles de ambos equipos.'])
                    ->with('security_alert', 'Intentaste guardar un resultado incompleto.')
                    ->withInput();
            }

            $partidos->get((int) $partidoId)->finalizarPartido(
                (int) $golesLocal,
                (int) $golesVisitante,
            );
        }

        return redirect()
            ->route('superadmin.resultados.edit')
            ->with('status', 'Resultados guardados correctamente.');
    }
}
