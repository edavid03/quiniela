<?php

namespace App\Services\WorldCup;

use App\Models\Equipo;
use App\Models\Partido;
use App\Support\WorldCup\Bracket2026;
use Illuminate\Support\Collection;

/**
 * Arma el cuadro de eliminatorias listo para la vista a partir de los partidos
 * existentes, sin tocar el schema.
 *
 * Estrategia:
 *  1. Ubica los partidos de dieciseisavos en su casilla por la PERTENENCIA DE
 *     GRUPO de sus equipos (dato estatico e infalible, independiente de calcular
 *     posiciones de grupo o desempates).
 *  2. Con la casilla de cada equipo ya conocida, propaga su camino por el arbol
 *     fijo y ubica octavos/cuartos/semis donde se cruzan los dos caminos.
 *  3. Determina ganadores por marcador o, si fue a penales (marcador igualado),
 *     por quien aparece en la ronda siguiente. Nunca inventa un ganador.
 *
 * Las casillas sin partido real muestran la etiqueta de posicion ("1o A",
 * "3o E/H/I/J") o "Por definir".
 */
class BracketResolver
{
    /** @var array<string, array{fase:string, home:array<string,mixed>, away:array<string,mixed>}> */
    private array $tpl;

    /** @var array<string, Partido> casilla => partido ubicado */
    private array $placed = [];

    /** @var array<int, string> equipoId => casilla de dieciseisavos */
    private array $r32SlotOf = [];

    /** @var array<int, int> equipoId => posicion en su grupo (1..4) */
    private array $positions = [];

    /** @var array<string, string> casilla hija => casilla padre (por ganador) */
    private array $winnerParent = [];

    /** @var array<int, Equipo> equipoId => equipo (para lookup por id) */
    private array $teamsById = [];

    /** @var list<int> ids de partidos que no se pudieron ubicar */
    private array $unplaced = [];

    /**
     * @param  Collection<int, Partido>  $partidos  todos, con local/visitante cargados
     * @return array{
     *   left: list<array{fase:string, label:string, matches:list<array<string,mixed>>}>,
     *   right: list<array{fase:string, label:string, matches:list<array<string,mixed>>}>,
     *   final: array<string,mixed>,
     *   third: array<string,mixed>,
     *   unplaced: list<int>
     * }
     */
    public function resolve(Collection $partidos): array
    {
        $this->tpl = Bracket2026::slots();
        $this->placed = [];
        $this->r32SlotOf = [];
        $this->teamsById = [];
        $this->unplaced = [];
        $this->buildParentMap();
        $this->positions = $this->computeStandings($partidos);

        $knockout = $partidos->filter(fn (Partido $p) => $p->fase !== 'Grupos');

        foreach ($knockout as $p) {
            if ($p->local) {
                $this->teamsById[$p->local_id] = $p->local;
            }
            if ($p->visitante) {
                $this->teamsById[$p->visitante_id] = $p->visitante;
            }
        }

        // 1. Dieciseisavos primero: definen el camino de cada equipo.
        foreach ($knockout->where('fase', Bracket2026::FASE_R32) as $p) {
            $this->placeR32($p);
        }

        // 2. El resto por interseccion de caminos.
        foreach ($knockout as $p) {
            if ($p->fase === Bracket2026::FASE_R32) {
                continue;
            }
            $this->placeLater($p);
        }

        return $this->build();
    }

    private function buildParentMap(): void
    {
        foreach ($this->tpl as $key => $slot) {
            foreach (['home', 'away'] as $side) {
                if (($slot[$side]['type'] ?? null) === 'winner') {
                    $this->winnerParent[$slot[$side]['slot']] = $key;
                }
            }
        }
    }

    private function placeR32(Partido $p): void
    {
        $ga = $p->local?->grupo;
        $gb = $p->visitante?->grupo;

        if ($ga === null || $gb === null) {
            $this->unplaced[] = $p->id;

            return;
        }

        $r32 = array_filter($this->tpl, fn (array $slot) => $slot['fase'] === Bracket2026::FASE_R32);

        // Primer pase por grupo. Para los pares espejo (mismas dos llaves, ej.
        // casillas 75/76 o 80/83) la pertenencia de grupo no alcanza.
        $matches = [];
        foreach ($r32 as $key => $slot) {
            if ($this->slotAcceptsGroups($slot, $ga, $gb)) {
                $matches[] = $key;
            }
        }

        // Si hay ambiguedad, desempata con la posicion de grupo (1o/2o/3o).
        if (count($matches) > 1) {
            $pa = $this->positions[$p->local_id] ?? null;
            $pb = $this->positions[$p->visitante_id] ?? null;
            $exact = array_values(array_filter(
                $matches,
                fn (string $key) => $this->slotAcceptsExact($this->tpl[$key], $ga, $pa, $gb, $pb)
            ));
            if (count($exact) === 1) {
                $matches = $exact;
            }
        }

        if ($matches === []) {
            $this->unplaced[] = $p->id;

            return;
        }

        $key = $matches[0];
        $this->placed[$key] = $p;
        $this->r32SlotOf[$p->local_id] = $key;
        $this->r32SlotOf[$p->visitante_id] = $key;
    }

    private function slotAcceptsGroups(array $slot, string $ga, string $gb): bool
    {
        return ($this->sourceAccepts($slot['home'], $ga) && $this->sourceAccepts($slot['away'], $gb))
            || ($this->sourceAccepts($slot['home'], $gb) && $this->sourceAccepts($slot['away'], $ga));
    }

    private function sourceAccepts(array $source, string $grupo): bool
    {
        return match ($source['type']) {
            'group' => $source['grupo'] === $grupo,
            'thirds' => in_array($grupo, $source['grupos'], true),
            default => false,
        };
    }

    private function slotAcceptsExact(array $slot, string $ga, ?int $pa, string $gb, ?int $pb): bool
    {
        return ($this->sourceAcceptsExact($slot['home'], $ga, $pa) && $this->sourceAcceptsExact($slot['away'], $gb, $pb))
            || ($this->sourceAcceptsExact($slot['home'], $gb, $pb) && $this->sourceAcceptsExact($slot['away'], $ga, $pa));
    }

    private function sourceAcceptsExact(array $source, string $grupo, ?int $pos): bool
    {
        return match ($source['type']) {
            'group' => $source['grupo'] === $grupo && $source['pos'] === $pos,
            'thirds' => $pos === 3 && in_array($grupo, $source['grupos'], true),
            default => false,
        };
    }

    /**
     * Posicion de cada equipo en su grupo a partir de los resultados de la fase
     * de grupos (puntos -> diferencia de gol -> goles a favor -> id). No aplica
     * desempates oficiales (head-to-head, fair play): solo desambigua casillas.
     *
     * @param  Collection<int, Partido>  $partidos
     * @return array<int, int> equipoId => posicion (1..4)
     */
    private function computeStandings(Collection $partidos): array
    {
        $tally = [];
        foreach ($partidos as $p) {
            if ($p->fase !== 'Grupos' || $p->goles_local === null || $p->goles_visitante === null) {
                continue;
            }
            foreach ([[$p->local, $p->goles_local, $p->goles_visitante], [$p->visitante, $p->goles_visitante, $p->goles_local]] as [$equipo, $gf, $ga]) {
                if ($equipo === null) {
                    continue;
                }
                $tally[$equipo->id] ??= ['grupo' => $equipo->grupo, 'pts' => 0, 'gf' => 0, 'ga' => 0];
                $tally[$equipo->id]['gf'] += $gf;
                $tally[$equipo->id]['ga'] += $ga;
                $tally[$equipo->id]['pts'] += $gf > $ga ? 3 : ($gf === $ga ? 1 : 0);
            }
        }

        $byGroup = [];
        foreach ($tally as $id => $row) {
            $byGroup[$row['grupo']][$id] = $row;
        }

        $positions = [];
        foreach ($byGroup as $rows) {
            uasort($rows, function (array $a, array $b) {
                return [$b['pts'], $b['gf'] - $b['ga'], $b['gf']] <=> [$a['pts'], $a['gf'] - $a['ga'], $a['gf']];
            });
            $pos = 1;
            foreach (array_keys($rows) as $id) {
                $positions[$id] = $pos++;
            }
        }

        return $positions;
    }

    private function placeLater(Partido $p): void
    {
        if ($p->fase === Bracket2026::FASE_FINAL) {
            $this->placed['104'] = $p;

            return;
        }
        if ($p->fase === Bracket2026::FASE_3RD) {
            $this->placed['103'] = $p;

            return;
        }

        $chainA = $this->chain($p->local_id);
        $chainB = $this->chain($p->visitante_id);

        $candidates = array_values(array_intersect($chainA, $chainB, $this->slotsOfFase($p->fase)));

        if (count($candidates) !== 1) {
            $this->unplaced[] = $p->id;

            return;
        }

        $this->placed[$candidates[0]] = $p;
    }

    /**
     * Camino que recorreria un equipo ganando: su casilla de dieciseisavos y
     * todas las casillas a las que avanza por el arbol fijo.
     *
     * @return list<string>
     */
    private function chain(int $equipoId): array
    {
        $slot = $this->r32SlotOf[$equipoId] ?? null;
        if ($slot === null) {
            return [];
        }

        $chain = [$slot];
        while (isset($this->winnerParent[$slot])) {
            $slot = $this->winnerParent[$slot];
            $chain[] = $slot;
        }

        return $chain;
    }

    /** @return list<string> */
    private function slotsOfFase(string $fase): array
    {
        $keys = [];
        foreach ($this->tpl as $key => $slot) {
            if ($slot['fase'] === $fase) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    private function winnerId(string $slotKey): ?int
    {
        $p = $this->placed[$slotKey] ?? null;
        if ($p === null) {
            return null;
        }

        $gl = $p->goles_local;
        $gv = $p->goles_visitante;

        if ($gl !== null && $gv !== null) {
            if ($gl > $gv) {
                return $p->local_id;
            }
            if ($gv > $gl) {
                return $p->visitante_id;
            }
        }

        // Marcador igualado (penales) o sin marcador: quien avanzo aparece en la
        // ronda siguiente.
        $parent = $this->winnerParent[$slotKey] ?? null;
        $next = $parent !== null ? ($this->placed[$parent] ?? null) : null;
        if ($next !== null) {
            $ids = [$next->local_id, $next->visitante_id];
            if (in_array($p->local_id, $ids, true)) {
                return $p->local_id;
            }
            if (in_array($p->visitante_id, $ids, true)) {
                return $p->visitante_id;
            }
        }

        return null;
    }

    private function loserId(string $slotKey): ?int
    {
        $p = $this->placed[$slotKey] ?? null;
        $winner = $this->winnerId($slotKey);
        if ($p === null || $winner === null) {
            return null;
        }

        return $winner === $p->local_id ? $p->visitante_id : $p->local_id;
    }

    private function build(): array
    {
        $layout = Bracket2026::layout();

        $mapColumns = fn (array $columns) => array_map(fn (array $col) => [
            'fase' => $col['fase'],
            'label' => $col['fase'],
            'matches' => array_map(fn (string $key) => $this->slotView($key), $col['slots']),
        ], $columns);

        return [
            'left' => $mapColumns($layout['left']),
            'right' => $mapColumns($layout['right']),
            'final' => $this->slotView($layout['final']),
            'third' => $this->slotView($layout['third']),
            'unplaced' => $this->unplaced,
        ];
    }

    /** @return array<string,mixed> */
    private function slotView(string $key): array
    {
        $tpl = $this->tpl[$key];
        $p = $this->placed[$key] ?? null;

        if ($p !== null) {
            $winner = $this->winnerId($key);

            return [
                'key' => $key,
                'fase' => $tpl['fase'],
                'home' => [
                    'team' => $p->local,
                    'label' => $p->local->name ?? $this->sourceLabel($tpl['home']),
                    'goles' => $p->goles_local,
                    'winner' => $winner !== null && $winner === $p->local_id,
                ],
                'away' => [
                    'team' => $p->visitante,
                    'label' => $p->visitante->name ?? $this->sourceLabel($tpl['away']),
                    'goles' => $p->goles_visitante,
                    'winner' => $winner !== null && $winner === $p->visitante_id,
                ],
                'fecha_utc' => $p->fecha_utc,
            ];
        }

        return [
            'key' => $key,
            'fase' => $tpl['fase'],
            'home' => $this->pendingSide($tpl['home']),
            'away' => $this->pendingSide($tpl['away']),
            'fecha_utc' => null,
        ];
    }

    /** @return array<string,mixed> */
    private function pendingSide(array $source): array
    {
        $team = null;

        if ($source['type'] === 'winner') {
            $team = $this->teamsById[$this->winnerId($source['slot'])] ?? null;
        } elseif ($source['type'] === 'loser') {
            $team = $this->teamsById[$this->loserId($source['slot'])] ?? null;
        }

        return [
            'team' => $team,
            'label' => $team?->name ?? $this->sourceLabel($source),
            'goles' => null,
            'winner' => false,
        ];
    }

    private function sourceLabel(array $source): string
    {
        return match ($source['type']) {
            'group' => $source['pos'].'º '.$source['grupo'],
            'thirds' => '3º '.implode('/', $source['grupos']),
            default => 'Por definir',
        };
    }
}
