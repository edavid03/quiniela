<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Partido;
use App\Services\WorldCup\BracketResolver;
use App\Support\WorldCup\Bracket2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrucesTest extends TestCase
{
    use RefreshDatabase;

    private int $nextTeamId = 1;

    public function test_template_has_32_slots(): void
    {
        // Partidos 73-104 de la numeracion FIFA.
        $this->assertCount(32, Bracket2026::slots());
    }

    public function test_r32_match_is_placed_by_group_membership(): void
    {
        // 2o A vs 2o B => casilla 73.
        $a = $this->team('A', 'Equipo A');
        $b = $this->team('B', 'Equipo B');
        $this->knockout(Bracket2026::FASE_R32, $a, $b, 2, 1);

        $bracket = $this->resolve();
        $slot = $this->findSlot($bracket, '73');

        $this->assertSame('Equipo A', $slot['home']['label']);
        $this->assertSame('Equipo B', $slot['away']['label']);
        $this->assertSame(2, $slot['home']['goles']);
        $this->assertTrue($slot['home']['winner']);
        $this->assertFalse($slot['away']['winner']);
        $this->assertEmpty($bracket['unplaced']);
    }

    public function test_winner_propagates_to_next_round_slot(): void
    {
        $a = $this->team('A', 'Equipo A');
        $b = $this->team('B', 'Equipo B');
        $this->knockout(Bracket2026::FASE_R32, $a, $b, 3, 0); // gana A en casilla 73

        $bracket = $this->resolve();

        // La casilla 73 alimenta la 90 (octavos). El ganador debe aparecer ahi.
        $r16 = $this->findSlot($bracket, '90');
        $this->assertSame('Equipo A', $r16['home']['label']);
        $this->assertNotNull($r16['home']['team']);
    }

    public function test_penalty_winner_is_read_from_next_round_match(): void
    {
        // 73: 2oA vs 2oB termina 1-1 (penales). 75: 1oF vs 2oC, gana F.
        $a = $this->team('A', 'Equipo A');
        $b = $this->team('B', 'Equipo B');
        $f = $this->team('F', 'Equipo F');
        $c = $this->team('C', 'Equipo C');
        $this->knockout(Bracket2026::FASE_R32, $a, $b, 1, 1);
        $this->knockout(Bracket2026::FASE_R32, $f, $c, 2, 0);

        // Octavos casilla 90 = ganador(73) vs ganador(75) = A vs F.
        $this->knockout(Bracket2026::FASE_R16, $a, $f, null, null);

        $bracket = $this->resolve();
        $slot73 = $this->findSlot($bracket, '73');

        // Aunque el marcador fue 1-1, A avanzo (aparece en la 90) => A es ganador.
        $this->assertTrue($slot73['home']['winner']);
        $this->assertFalse($slot73['away']['winner']);
    }

    public function test_empty_bracket_renders_full_skeleton_with_labels(): void
    {
        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);

        $this->actingAs($user)
            ->get(route('liga.cruces.index', $liga))
            ->assertOk()
            ->assertSee('Dieciseisavos')
            ->assertSee('Tercer puesto')
            ->assertSee('Por definir');
    }

    public function test_route_requires_authentication(): void
    {
        $liga = $this->createLiga();

        $this->get(route('liga.cruces.index', $liga))
            ->assertRedirect(route('liga.login', $liga));
    }

    private function resolve(): array
    {
        $partidos = Partido::query()->with(['local', 'visitante'])->orderBy('fecha_utc')->get();

        return app(BracketResolver::class)->resolve($partidos);
    }

    private function team(string $grupo, string $name): Equipo
    {
        $id = $this->nextTeamId++;

        return Equipo::create([
            'id' => $id,
            'name' => $name,
            'code' => str_pad((string) $id, 3, 'A', STR_PAD_LEFT),
            'grupo' => $grupo,
        ]);
    }

    private function knockout(string $fase, Equipo $local, Equipo $visitante, ?int $gl, ?int $gv): Partido
    {
        return Partido::create([
            'local_id' => $local->id,
            'visitante_id' => $visitante->id,
            'fecha_utc' => now()->utc()->addDays($this->nextTeamId)->format('Y-m-d H:i:s'),
            'estadio' => 'Estadio',
            'fase' => $fase,
            'goles_local' => $gl,
            'goles_visitante' => $gv,
        ]);
    }

    private function findSlot(array $bracket, string $key): array
    {
        foreach (['left', 'right'] as $half) {
            foreach ($bracket[$half] as $col) {
                foreach ($col['matches'] as $match) {
                    if ($match['key'] === $key) {
                        return $match;
                    }
                }
            }
        }

        foreach (['final', 'third'] as $single) {
            if ($bracket[$single]['key'] === $key) {
                return $bracket[$single];
            }
        }

        $this->fail("Slot {$key} no encontrado en el bracket.");
    }
}
