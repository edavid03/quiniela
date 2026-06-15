<?php

namespace App\Services\Football;

/**
 * Representacion neutra de un partido tal como lo expone una API de resultados.
 *
 * Desacopla el motor de scoring de la forma del JSON de cada proveedor: cambiar
 * de football-data.org a otra API solo requiere otra implementacion de
 * FootballDataProvider que produzca estos DTOs.
 */
final class MatchDto
{
    public function __construct(
        public readonly int $apiId,
        public readonly string $stage,
        public readonly ?string $utcDate,
        public readonly string $status,
        public readonly ?int $homeApiId,
        public readonly ?int $awayApiId,
        public readonly ?string $homeTla,
        public readonly ?string $awayTla,
        public readonly ?int $homeGoals,
        public readonly ?int $awayGoals,
        public readonly ?string $winner,
        public readonly ?string $venue,
    ) {}

    public function estaFinalizado(): bool
    {
        return $this->status === 'FINISHED'
            && $this->homeGoals !== null
            && $this->awayGoals !== null;
    }

    public function esFaseDeGrupos(): bool
    {
        return $this->stage === 'GROUP_STAGE';
    }

    public function equiposDefinidos(): bool
    {
        return $this->homeApiId !== null && $this->awayApiId !== null;
    }
}
