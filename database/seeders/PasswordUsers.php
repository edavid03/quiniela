<?php

namespace Database\Seeders;

use App\Models\Liga;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordUsers extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $updated = 0;

            $updated += User::withoutGlobalScopes()
                ->where('email', 'superadmin@quiniela.test')
                ->update([
                    'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'Quiniela2026-dev')),
                ]);

            $liga = Liga::where('slug', 'demo')->first();

            if ($liga === null) {
                $this->command?->warn('Liga demo not found. Demo users were not updated.');
                $this->command?->info("Updated {$updated} user password(s).");

                return;
            }

            $updated += User::withoutGlobalScopes()
                ->where('liga_id', $liga->id)
                ->whereIn('username', ['admin', 'jugador'])
                ->update([
                    'password' => Hash::make('Quiniela2026'),
                ]);

            $this->command?->info("Updated {$updated} user password(s).");
        });
    }
}
