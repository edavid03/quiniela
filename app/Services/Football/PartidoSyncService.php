<?php

namespace App\Services\Football;

use App\Models\Equipo;
use App\Models\Partido;
use Carbon\Carbon;

/**
 * Sincroniza los partidos del Mundial contra un FootballDataProvider:
 *  - Fase de grupos: backfillea el api_id sobre los partidos ya seedeados.
 *  - Eliminatorias: crea el partido en cuanto la API define ambos equipos.
 *  - Finaliza (puntua) los partidos terminados reutilizando finalizarPartido().
 *
 * Es idempotente: solo finaliza cuando el marcador cambia, para no resetear
 * los puntos de todas las predicciones en cada corrida.
 */
class PartidoSyncService
{
    /**
     * Mapeo de la fase de la API al nombre en espanol usado en la quiniela.
     */
    private const FASES = [
        'LAST_16' => 'Octavos',
        'QUARTER_FINALS' => 'Cuartos',
        'SEMI_FINALS' => 'Semifinal',
        'THIRD_PLACE' => 'Tercer puesto',
        'FINAL' => 'Final',
    ];

    public function __construct(private readonly FootballDataProvider $provider) {}

    public function sync(): SyncReport
    {
        $report = new SyncReport;

        foreach ($this->provider->matches() as $match) {
            $this->syncMatch($match, $report);
        }

        return $report;
    }

    private function syncMatch(MatchDto $match, SyncReport $report): void
    {
        $local = $match->homeApiId === null ? null : Equipo::firstWhere('api_id', $match->homeApiId);
        $visitante = $match->awayApiId === null ? null : Equipo::firstWhere('api_id', $match->awayApiId);

        $partido = Partido::firstWhere('api_id', $match->apiId);

        if ($partido === null) {
            $partido = $this->resolverPartido($match, $local, $visitante, $report);

            if ($partido === null) {
                return; // sin mapear o equipos sin definir; ya contabilizado.
            }
        }

        $this->finalizarSiCorresponde($partido, $match, $report);
    }

    private function resolverPartido(MatchDto $match, ?Equipo $local, ?Equipo $visitante, SyncReport $report): ?Partido
    {
        if ($local === null || $visitante === null) {
            // En grupos el partido ya existe pero no pudimos resolver equipos;
            // en knockout aun no estan definidos los clasificados.
            $match->esFaseDeGrupos() ? $report->sinMapear++ : $report->tbd++;

            return null;
        }

        if ($match->esFaseDeGrupos()) {
            return $this->backfillGrupos($match, $local, $visitante, $report);
        }

        return $this->crearKnockout($match, $local, $visitante, $report);
    }

    private function backfillGrupos(MatchDto $match, Equipo $local, Equipo $visitante, SyncReport $report): ?Partido
    {
        $partido = Partido::query()
            ->where('local_id', $local->id)
            ->where('visitante_id', $visitante->id)
            ->whereNull('api_id')
            ->first();

        if ($partido === null) {
            $report->sinMapear++;

            return null;
        }

        $partido->update(['api_id' => $match->apiId]);

        return $partido;
    }

    private function crearKnockout(MatchDto $match, Equipo $local, Equipo $visitante, SyncReport $report): Partido
    {
        $partido = Partido::create([
            'api_id' => $match->apiId,
            'local_id' => $local->id,
            'visitante_id' => $visitante->id,
            'fecha_utc' => Carbon::parse($match->utcDate)->utc()->format('Y-m-d H:i:s'),
            'estadio' => $match->venue,
            'fase' => self::FASES[$match->stage] ?? $match->stage,
        ]);

        $report->creados++;

        return $partido;
    }

    private function finalizarSiCorresponde(Partido $partido, MatchDto $match, SyncReport $report): void
    {
        if (! $match->estaFinalizado()) {
            return;
        }

        // Manual blinda: un resultado cargado a mano no se sobrescribe nunca.
        if ($partido->resultado_origen === Partido::ORIGEN_MANUAL) {
            $report->bloqueados++;

            return;
        }

        $sinCambios = $partido->goles_local !== null
            && (int) $partido->goles_local === $match->homeGoals
            && (int) $partido->goles_visitante === $match->awayGoals;

        if ($sinCambios) {
            $report->ignorados++;

            return;
        }

        $partido->finalizarPartido($match->homeGoals, $match->awayGoals, Partido::ORIGEN_API);
        $report->finalizados++;
    }
}
