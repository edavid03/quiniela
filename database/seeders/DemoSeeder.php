<?php

namespace Database\Seeders;

use App\Models\Liga;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Cuentas demo + superadmin. Idempotente y NO destructivo: solo usa
     * updateOrCreate sobre users/ligas, nunca toca partidos/equipos/predicciones.
     * Seguro de re-correr en cada deploy (a diferencia de DatabaseSeeder completo,
     * que llama a Mundial2026Seeder y borra partidos).
     */
    public function run(): void
    {
        // Superadmin global: cuenta de devs, no aparece en ninguna liga.
        // Clave por entorno (Dokploy); el fallback solo sirve para dev local.
        User::updateOrCreate(
            ['email' => 'superadmin@quiniela.test'],
            [
                'liga_id' => null,
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'Quiniela2026-dev')),
                'role' => User::ROLE_SUPERADMIN,
            ]
        );

        // Liga demo para desarrollo local / demos.
        $liga = Liga::updateOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Liga Demo', 'is_active' => true],
        );

        // Cuentas demo listas para usar: se marcan verificadas (figuran "Activo").
        tap(User::updateOrCreate(
            ['liga_id' => $liga->id, 'username' => 'admin'],
            [
                'name' => 'Admin Demo',
                'email' => 'admin@demo.test',
                'password' => Hash::make('Quiniela2026'),
                'role' => User::ROLE_LIGA_ADMIN,
            ]
        ), fn (User $u) => $u->forceFill(['email_verified_at' => now()])->save());

        tap(User::updateOrCreate(
            ['liga_id' => $liga->id, 'username' => 'jugador'],
            [
                'name' => 'Jugador Demo',
                'email' => 'jugador@demo.test',
                'password' => Hash::make('Quiniela2026'),
                'role' => User::ROLE_LIGA_USER,
            ]
        ), fn (User $u) => $u->forceFill(['email_verified_at' => now()])->save());
    }
}
