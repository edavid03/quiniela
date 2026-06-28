<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // El sync solo asigna la fase al crear el partido; los knockout creados
        // antes de mapear LAST_32 quedaron con el codigo crudo de la API.
        DB::table('partidos')
            ->where('fase', 'LAST_32')
            ->update(['fase' => 'Dieciseisavos']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('partidos')
            ->where('fase', 'Dieciseisavos')
            ->update(['fase' => 'LAST_32']);
    }
};
