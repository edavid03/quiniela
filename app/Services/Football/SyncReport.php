<?php

namespace App\Services\Football;

/**
 * Contadores de una corrida de sincronizacion, para logging y salida del comando.
 */
final class SyncReport
{
    public int $creados = 0;

    public int $finalizados = 0;

    public int $ignorados = 0;

    public int $sinMapear = 0;

    public int $tbd = 0;

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'creados' => $this->creados,
            'finalizados' => $this->finalizados,
            'ignorados' => $this->ignorados,
            'sin_mapear' => $this->sinMapear,
            'tbd' => $this->tbd,
        ];
    }
}
