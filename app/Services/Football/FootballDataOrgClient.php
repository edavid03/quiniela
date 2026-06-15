<?php

namespace App\Services\Football;

use Illuminate\Support\Facades\Http;

/**
 * Cliente de football-data.org (https://www.football-data.org/documentation/api).
 *
 * Endpoint: GET /v4/competitions/{WC}/matches con header X-Auth-Token.
 * Una sola request por corrida devuelve todos los partidos del torneo.
 */
class FootballDataOrgClient implements FootballDataProvider
{
    /**
     * @return array<int, MatchDto>
     */
    public function matches(): array
    {
        $competition = config('services.football_data.competition');

        $response = Http::baseUrl((string) config('services.football_data.base_url'))
            ->withHeaders(['X-Auth-Token' => (string) config('services.football_data.token')])
            ->get("/competitions/{$competition}/matches")
            ->throw();

        return collect($response->json('matches', []))
            ->map(fn (array $match): MatchDto => $this->toDto($match))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $match
     */
    private function toDto(array $match): MatchDto
    {
        return new MatchDto(
            apiId: (int) $match['id'],
            stage: (string) ($match['stage'] ?? ''),
            utcDate: $match['utcDate'] ?? null,
            status: (string) ($match['status'] ?? ''),
            homeApiId: $this->intOrNull($match['homeTeam']['id'] ?? null),
            awayApiId: $this->intOrNull($match['awayTeam']['id'] ?? null),
            homeTla: $match['homeTeam']['tla'] ?? null,
            awayTla: $match['awayTeam']['tla'] ?? null,
            homeGoals: $this->intOrNull($match['score']['fullTime']['home'] ?? null),
            awayGoals: $this->intOrNull($match['score']['fullTime']['away'] ?? null),
            winner: $match['score']['winner'] ?? null,
            venue: $match['venue'] ?? null,
        );
    }

    private function intOrNull(mixed $value): ?int
    {
        return $value === null ? null : (int) $value;
    }
}
