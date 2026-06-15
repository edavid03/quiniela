<?php

namespace App\Services\Football;

/**
 * Contrato de un proveedor de resultados del Mundial. La implementacion por
 * defecto es football-data.org; si su tier free no cubre el torneo se puede
 * swappear el bind por otra (API-Football) sin tocar el sync.
 */
interface FootballDataProvider
{
    /**
     * @return array<int, MatchDto>
     */
    public function matches(): array;
}
