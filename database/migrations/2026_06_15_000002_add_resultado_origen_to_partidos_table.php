<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->string('resultado_origen', 10)->nullable()->after('goles_visitante');
        });

        // Todo lo cargado antes de esta feature fue, por definicion, manual: el
        // importer aun no escribia el origen. Quedan blindados del auto-sync.
        DB::table('partidos')
            ->whereNotNull('goles_local')
            ->update(['resultado_origen' => 'manual']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->dropColumn('resultado_origen');
        });
    }
};
