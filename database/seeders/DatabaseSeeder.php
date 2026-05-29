<?php

namespace Database\Seeders;

use App\Models\Liga;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Partidos y equipos globales (compartidos por todas las ligas).
        $this->call(Mundial2026Seeder::class);

        // Superadmin global: no pertenece a ninguna liga, entra por email.
        User::updateOrCreate(
            ['email' => 'superadmin@quiniela.test'],
            [
                'liga_id' => null,
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPERADMIN,
            ]
        );

        // Liga demo para desarrollo local.
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
                'password' => Hash::make('password'),
                'role' => User::ROLE_LIGA_ADMIN,
            ]
        ), fn (User $u) => $u->forceFill(['email_verified_at' => now()])->save());

        tap(User::updateOrCreate(
            ['liga_id' => $liga->id, 'username' => 'jugador'],
            [
                'name' => 'Jugador Demo',
                'email' => 'jugador@demo.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_LIGA_USER,
            ]
        ), fn (User $u) => $u->forceFill(['email_verified_at' => now()])->save());
    }
}
