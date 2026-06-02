<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Partidos y equipos globales (compartidos por todas las ligas).
        // OJO: Mundial2026Seeder es DESTRUCTIVO (borra partidos/equipos), por eso
        // no debe correrse en cada deploy. El seed automático de prod usa solo
        // DemoSeeder (idempotente) vía `db:seed --class=DemoSeeder`.
        $this->call(Mundial2026Seeder::class);

        // Liga demo + cuentas (superadmin, admin, jugador).
        $this->call(DemoSeeder::class);
    }
}
