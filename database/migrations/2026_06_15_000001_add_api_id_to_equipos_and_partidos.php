<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // El id de la API externa (football-data.org) es la clave estable para
        // correlacionar equipos y partidos. NO se matchea por nombre: los
        // nombres vienen en ingles ("South Korea") y la BD en espanol.
        Schema::table('equipos', function (Blueprint $table) {
            $table->unsignedBigInteger('api_id')->nullable()->unique()->after('code');
        });

        Schema::table('partidos', function (Blueprint $table) {
            $table->unsignedBigInteger('api_id')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropUnique(['api_id']);
            $table->dropColumn('api_id');
        });

        Schema::table('partidos', function (Blueprint $table) {
            $table->dropUnique(['api_id']);
            $table->dropColumn('api_id');
        });
    }
};
