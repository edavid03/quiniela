<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Partido;
use App\Models\Prediccion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PartidosSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.football_data.token' => 'test-token']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $matches
     */
    private function fakeApi(array $matches, int $status = 200): void
    {
        Http::fake([
            '*/competitions/WC/matches' => Http::response(['matches' => $matches], $status),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function match(array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => 100,
            'utcDate' => '2026-06-11T19:00:00Z',
            'status' => 'FINISHED',
            'stage' => 'GROUP_STAGE',
            'group' => 'Group A',
            'homeTeam' => ['id' => 770, 'name' => 'Mexico', 'tla' => 'MEX'],
            'awayTeam' => ['id' => 805, 'name' => 'South Korea', 'tla' => 'KOR'],
            'score' => [
                'winner' => 'HOME_TEAM',
                'duration' => 'REGULAR',
                'fullTime' => ['home' => 2, 'away' => 1],
            ],
            'venue' => 'Estadio Azteca',
        ], $overrides);
    }

    private function equipo(int $id, string $code, ?int $apiId = null): Equipo
    {
        return Equipo::create([
            'id' => $id,
            'name' => "Equipo {$code}",
            'code' => $code,
            'grupo' => 'A',
            'api_id' => $apiId,
        ]);
    }

    // 1. Backfill mapea por TLA y sale 0 cuando todos quedan mapeados.
    public function test_backfill_maps_teams_by_tla_and_exits_zero(): void
    {
        $this->equipo(1, 'MEX');
        $this->equipo(3, 'KOR');

        $this->fakeApi([$this->match()]);

        $this->artisan('equipos:backfill-api-id')->assertExitCode(0);

        $this->assertDatabaseHas('equipos', ['id' => 1, 'api_id' => 770]);
        $this->assertDatabaseHas('equipos', ['id' => 3, 'api_id' => 805]);
    }

    // 1b. Si queda algun equipo sin mapear, sale 1 (bloqueo duro).
    public function test_backfill_exits_one_when_a_team_stays_unmapped(): void
    {
        $this->equipo(1, 'MEX');
        $this->equipo(3, 'KOR');
        $this->equipo(5, 'BRA'); // sin contraparte en la API

        $this->fakeApi([$this->match()]);

        $this->artisan('equipos:backfill-api-id')->assertExitCode(1);

        $this->assertDatabaseHas('equipos', ['id' => 5, 'api_id' => null]);
    }

    // 1c. Backfill resuelve los alias (TLA de la API != code FIFA local).
    public function test_backfill_maps_teams_via_alias(): void
    {
        $this->equipo(7, 'URU'); // FIFA local; la API lo trae como URY

        $this->fakeApi([$this->match([
            'awayTeam' => ['id' => 762, 'name' => 'Uruguay', 'tla' => 'URY'],
        ])]);
        $this->equipo(1, 'MEX'); // para que el home (MEX) tambien mapee y salga 0

        $this->artisan('equipos:backfill-api-id')->assertExitCode(0);

        $this->assertDatabaseHas('equipos', ['id' => 7, 'api_id' => 762]);
    }

    // 2. Grupos FINISHED: backfillea api_id del partido seeded y puntua.
    public function test_group_match_finished_backfills_api_id_and_scores(): void
    {
        $this->equipo(1, 'MEX', 770);
        $this->equipo(3, 'KOR', 805);
        $partido = $this->partidoGrupos(1, 3);

        $ligaA = $this->createLiga(['slug' => 'liga-a']);
        $ligaB = $this->createLiga(['slug' => 'liga-b']);
        $userA = $this->ligaUser($ligaA);
        $userB = $this->ligaUser($ligaB);

        $this->prediccion($ligaA, $userA, $partido, 2, 1); // exacto -> 3
        $this->prediccion($ligaB, $userB, $partido, 3, 0); // solo signo -> 1

        $this->fakeApi([$this->match(['id' => 555])]);

        $this->artisan('partidos:sync')->assertExitCode(0);

        $this->assertDatabaseHas('partidos', [
            'id' => $partido->id,
            'api_id' => 555,
            'goles_local' => 2,
            'goles_visitante' => 1,
        ]);
        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $userA->id, 'puntos' => 3, 'acertado' => true,
        ]);
        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $userB->id, 'puntos' => 1, 'acertado' => false,
        ]);
    }

    // 3. Idempotencia: una segunda corrida con el mismo marcador no re-evalua.
    public function test_sync_is_idempotent_and_does_not_rescore_unchanged_results(): void
    {
        $this->equipo(1, 'MEX', 770);
        $this->equipo(3, 'KOR', 805);
        $partido = $this->partidoGrupos(1, 3);

        $liga = $this->createLiga();
        $user = $this->ligaUser($liga);
        $pred = $this->prediccion($liga, $user, $partido, 2, 1);

        $this->fakeApi([$this->match(['id' => 555])]);
        $this->artisan('partidos:sync')->assertExitCode(0);

        // Centinela: si el sync vuelve a finalizar, evaluarResultado lo pisaria.
        DB::table('predicciones')->where('id', $pred->id)->update(['puntos' => 99]);

        $this->artisan('partidos:sync')->assertExitCode(0);

        $this->assertDatabaseHas('predicciones', ['id' => $pred->id, 'puntos' => 99]);
    }

    // 4. Knockout con equipos sin definir (TBD): no se crea el partido.
    public function test_knockout_with_undefined_teams_is_not_created(): void
    {
        $this->equipo(1, 'MEX', 770);

        $this->fakeApi([$this->match([
            'id' => 900,
            'status' => 'SCHEDULED',
            'stage' => 'LAST_16',
            'awayTeam' => ['id' => null, 'name' => null, 'tla' => null],
            'score' => ['winner' => null, 'fullTime' => ['home' => null, 'away' => null]],
        ])]);

        $this->artisan('partidos:sync')->assertExitCode(0);

        $this->assertDatabaseMissing('partidos', ['api_id' => 900]);
    }

    // 5. Knockout con ambos equipos definidos: se crea con fase mapeada y abierto.
    public function test_knockout_with_defined_teams_is_created_open_for_betting(): void
    {
        Carbon::setTestNow('2026-07-01 00:00:00');

        $this->equipo(1, 'MEX', 770);
        $this->equipo(3, 'KOR', 805);

        $this->fakeApi([$this->match([
            'id' => 901,
            'utcDate' => '2026-07-04T19:00:00Z',
            'status' => 'TIMED',
            'stage' => 'LAST_16',
            'venue' => 'MetLife Stadium',
            'score' => ['winner' => null, 'fullTime' => ['home' => null, 'away' => null]],
        ])]);

        $this->artisan('partidos:sync')->assertExitCode(0);

        $this->assertDatabaseHas('partidos', [
            'api_id' => 901,
            'local_id' => 1,
            'visitante_id' => 3,
            'fase' => 'Octavos',
            'estadio' => 'MetLife Stadium',
            'goles_local' => null,
        ]);

        $partido = Partido::where('api_id', 901)->first();
        $this->assertNotNull($partido->fecha_caracas);
        $this->assertTrue($partido->admitePronosticos());
    }

    // 6. Empate por penales: el ganador por penales no cambia el signo (goles).
    public function test_penalty_winner_is_scored_as_a_draw(): void
    {
        $this->equipo(1, 'MEX', 770);
        $this->equipo(3, 'KOR', 805);
        $partido = $this->partidoGrupos(1, 3);

        $liga = $this->createLiga();
        $userExacto = $this->ligaUser($liga);
        $userSigno = $this->ligaUser($liga);
        $this->prediccion($liga, $userExacto, $partido, 1, 1); // exacto empate -> 3
        $this->prediccion($liga, $userSigno, $partido, 2, 0);  // gana local -> 0

        $this->fakeApi([$this->match([
            'id' => 556,
            'score' => [
                'winner' => 'HOME_TEAM', // gano por penales
                'duration' => 'PENALTIES',
                'fullTime' => ['home' => 1, 'away' => 1],
            ],
        ])]);

        $this->artisan('partidos:sync')->assertExitCode(0);

        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $userExacto->id, 'puntos' => 3, 'acertado' => true,
        ]);
        $this->assertDatabaseHas('predicciones', [
            'usuario_id' => $userSigno->id, 'puntos' => 0, 'acertado' => false,
        ]);
    }

    // 6b. El sync marca como "api" (oficial) los partidos que finaliza.
    public function test_sync_marks_finalized_match_as_official(): void
    {
        $this->equipo(1, 'MEX', 770);
        $this->equipo(3, 'KOR', 805);
        $partido = $this->partidoGrupos(1, 3);

        $this->fakeApi([$this->match(['id' => 600])]);
        $this->artisan('partidos:sync')->assertExitCode(0);

        $this->assertDatabaseHas('partidos', [
            'id' => $partido->id,
            'goles_local' => 2,
            'resultado_origen' => 'api',
        ]);
    }

    // 6c. Manual blinda: un resultado cargado a mano no lo sobrescribe el sync.
    public function test_manual_result_is_not_overwritten_by_sync(): void
    {
        $this->equipo(1, 'MEX', 770);
        $this->equipo(3, 'KOR', 805);
        $partido = $this->partidoGrupos(1, 3);
        $partido->finalizarPartido(0, 0); // manual por default, distinto al 2-1 de la API

        $this->fakeApi([$this->match(['id' => 601])]);
        $this->artisan('partidos:sync')->assertExitCode(0);

        $this->assertDatabaseHas('partidos', [
            'id' => $partido->id,
            'goles_local' => 0,
            'goles_visitante' => 0,
            'resultado_origen' => 'manual',
        ]);
    }

    // 7. Equipo sin mapear: se saltea sin romper.
    public function test_unmapped_team_is_skipped_without_error(): void
    {
        $this->equipo(1, 'MEX', 770);
        $this->equipo(3, 'KOR'); // existe pero SIN api_id: la API trae 805, nadie lo tiene
        $partido = $this->partidoGrupos(1, 3);

        $this->fakeApi([$this->match(['id' => 557])]);

        $this->artisan('partidos:sync')->assertExitCode(0);

        $this->assertDatabaseHas('partidos', [
            'id' => $partido->id,
            'api_id' => null,
            'goles_local' => null,
        ]);
    }

    // 8. Error HTTP de la API: sale 1 y no toca la base.
    public function test_http_error_exits_one_and_changes_nothing(): void
    {
        $this->equipo(1, 'MEX', 770);
        $this->equipo(3, 'KOR', 805);
        $partido = $this->partidoGrupos(1, 3);

        $this->fakeApi([], 403);

        $this->artisan('partidos:sync')->assertExitCode(1);

        $this->assertDatabaseHas('partidos', [
            'id' => $partido->id,
            'goles_local' => null,
        ]);
    }

    private function partidoGrupos(int $localId, int $visitanteId): Partido
    {
        return Partido::create([
            'local_id' => $localId,
            'visitante_id' => $visitanteId,
            'fecha_utc' => '2026-06-11 19:00:00',
            'estadio' => 'Estadio Azteca',
            'fase' => 'Grupos',
            'goles_local' => null,
            'goles_visitante' => null,
        ]);
    }

    private function prediccion($liga, $user, Partido $partido, int $gl, int $gv): Prediccion
    {
        return Prediccion::create([
            'liga_id' => $liga->id,
            'usuario_id' => $user->id,
            'partido_id' => $partido->id,
            'goles_local' => $gl,
            'goles_visitante' => $gv,
            'acertado' => false,
            'puntos' => null,
        ]);
    }
}
