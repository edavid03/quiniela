<?php

namespace App\Console\Commands;

use App\Services\Football\PartidoSyncService;
use App\Services\Football\SyncReport;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartidosSync extends Command
{
    protected $signature = 'partidos:sync {--dry-run : Simula la sincronizacion sin escribir en la base}';

    protected $description = 'Importa resultados del Mundial desde la API y finaliza los partidos terminados';

    public function handle(PartidoSyncService $service): int
    {
        try {
            $report = $this->option('dry-run')
                ? $this->dryRun($service)
                : $service->sync();
        } catch (RequestException|ConnectionException $e) {
            Log::error('partidos:sync fallo al consultar la API', ['error' => $e->getMessage()]);
            $this->error('No se pudieron obtener los resultados: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->table(array_keys($report->toArray()), [$report->toArray()]);

        return self::SUCCESS;
    }

    private function dryRun(PartidoSyncService $service): SyncReport
    {
        DB::beginTransaction();

        try {
            return $service->sync();
        } finally {
            DB::rollBack();
        }
    }
}
