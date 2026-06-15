<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use App\Services\Football\FootballDataProvider;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;

class EquiposBackfillApiId extends Command
{
    protected $signature = 'equipos:backfill-api-id {--dry-run : Muestra el mapeo sin escribir en la base}';

    protected $description = 'Mapea cada equipo de la quiniela con su id en la API (football-data.org) usando el codigo FIFA (TLA)';

    /**
     * Equipos cuyo TLA en la API difiere del code FIFA usado en la quiniela.
     * Clave = TLA de la API, valor = code local. Se completa con los no-mapeados
     * que reporte una corrida real.
     *
     * @var array<string, string>
     */
    private const ALIAS = [
        'URY' => 'URU', // Uruguay: la API usa ISO (URY), la quiniela FIFA (URU)
        'CUR' => 'CUW', // Curazao: la API alterna CUR/CUW entre llamadas; el id es estable
    ];

    public function handle(FootballDataProvider $provider): int
    {
        try {
            $apiTeams = $this->equiposDeLaApi($provider);
        } catch (RequestException|ConnectionException $e) {
            $this->error('No se pudo consultar la API: '.$e->getMessage());

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $dryRun && DB::beginTransaction();

        $sinMatch = $this->mapear($apiTeams);

        if (! empty($sinMatch)) {
            $this->warn('Equipos de la API sin equipo local (TLA != code FIFA): corregir en const MANUAL.');
            $this->table(['api_id', 'tla'], array_map(
                fn (int $id, string $tla) => [$id, $tla],
                array_values($sinMatch),
                array_keys($sinMatch),
            ));
        }

        $faltantes = Equipo::whereNull('api_id')->orderBy('id')->get(['id', 'name', 'code']);

        if ($faltantes->isNotEmpty()) {
            $this->error("Quedan {$faltantes->count()} equipos sin mapear (corregir antes de habilitar knockouts):");
            $this->table(['id', 'name', 'code'], $faltantes->toArray());
            $dryRun && DB::rollBack();

            return self::FAILURE;
        }

        $dryRun && DB::rollBack();
        $this->info('Todos los equipos quedaron mapeados.');

        return self::SUCCESS;
    }

    /**
     * Pares unicos (tla => api_id) de los equipos definidos en la API.
     *
     * @return array<string, int>
     */
    private function equiposDeLaApi(FootballDataProvider $provider): array
    {
        $teams = [];

        foreach ($provider->matches() as $match) {
            if ($match->homeApiId !== null && $match->homeTla !== null) {
                $teams[$match->homeTla] = $match->homeApiId;
            }
            if ($match->awayApiId !== null && $match->awayTla !== null) {
                $teams[$match->awayTla] = $match->awayApiId;
            }
        }

        return $teams;
    }

    /**
     * Mapea cada equipo de la API contra el equipo local por code FIFA (con los
     * alias para los TLA que difieren). Devuelve los TLA que no matchearon.
     *
     * @param  array<string, int>  $apiTeams
     * @return array<string, int>
     */
    private function mapear(array $apiTeams): array
    {
        $sinMatch = [];

        foreach ($apiTeams as $tla => $apiId) {
            $code = self::ALIAS[$tla] ?? $tla;

            $actualizados = Equipo::where('code', $code)
                ->whereNull('api_id')
                ->update(['api_id' => $apiId]);

            $yaMapeado = Equipo::where('code', $code)->where('api_id', $apiId)->exists();

            if ($actualizados === 0 && ! $yaMapeado) {
                $sinMatch[$tla] = $apiId;
            }
        }

        return $sinMatch;
    }
}
