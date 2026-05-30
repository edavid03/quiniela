<?php

namespace Tests;

use App\Models\Equipo;
use App\Models\Liga;
use App\Models\Partido;
use App\Models\User;
use App\Support\Tenancy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // En php-fpm cada request arranca con Tenancy en null; PHPUnit comparte
        // el proceso, asi que lo reseteamos para que no se filtre entre tests.
        Tenancy::forget();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function createLiga(array $attrs = []): Liga
    {
        return Liga::factory()->create($attrs);
    }

    protected function ligaUser(Liga $liga, array $attrs = []): User
    {
        return User::factory()->forLiga($liga)->create($attrs);
    }

    protected function ligaAdmin(Liga $liga, array $attrs = []): User
    {
        return User::factory()->forLiga($liga)->ligaAdmin()->create($attrs);
    }

    protected function superAdmin(array $attrs = []): User
    {
        return User::factory()->superAdmin()->create($attrs);
    }

    /**
     * Crea un partido global (compartido por todas las ligas).
     */
    protected function crearPartido(int $localId = 1, int $visitanteId = 2, ?string $fechaUtc = null): Partido
    {
        $local = Equipo::firstOrCreate(['id' => $localId], [
            'name' => "Local {$localId} FC",
            'code' => 'LOC',
            'grupo' => 'A',
        ]);

        $visitante = Equipo::firstOrCreate(['id' => $visitanteId], [
            'name' => "Visitante {$visitanteId} FC",
            'code' => 'VIS',
            'grupo' => 'A',
        ]);

        return Partido::create([
            'local_id' => $local->id,
            'visitante_id' => $visitante->id,
            'fecha_utc' => $fechaUtc ?? now()->utc()->addWeeks(3)->format('Y-m-d H:i:s'),
            'estadio' => 'Estadio de Prueba',
            'fase' => 'Grupos',
            'goles_local' => null,
            'goles_visitante' => null,
        ]);
    }
}
