<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminPartidoResultadoController extends Controller
{
    // Cadencia del sync automatico (partidos:sync). Debe coincidir con el
    // intervalo del scheduler en routes/console.php (everyThirtySeconds()).
    private const SYNC_INTERVAL_SECONDS = 30;

    public function edit(): View
    {
        return view('admin.resultados', [
            'partidos' => Partido::query()
                ->with(['local', 'visitante'])
                ->orderBy('fecha_utc')
                ->get(),
            'ultimaSync' => $this->ultimaSync(),
            'syncIntervalSeconds' => self::SYNC_INTERVAL_SECONDS,
        ]);
    }

    // La card lee el last_run una sola vez al render; con sync cada 30s necesita
    // refrescarlo en vivo. Este endpoint liviano devuelve el estado actual para
    // que el front haga polling sin recargar la pagina (no perder el form).
    public function syncStatus(): JsonResponse
    {
        return response()->json([
            'last_run_epoch' => $this->ultimaSync()?->getTimestamp(),
            'interval_seconds' => self::SYNC_INTERVAL_SECONDS,
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

    private function ultimaSync(): ?Carbon
    {
        $value = DB::table('app_settings')
            ->where('key', 'partidos_sync_last_run')
            ->value('value');

        return $value ? Carbon::parse($value) : null;
    }
}
