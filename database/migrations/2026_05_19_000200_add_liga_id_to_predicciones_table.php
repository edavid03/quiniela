<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // liga_id denormalizado desde el user: permite scopear el ranking y el
        // motor de scoring por liga sin joins extra. El unico (partido_id,
        // usuario_id) existente sigue siendo correcto (usuario ya es por-liga).
        Schema::table('predicciones', function (Blueprint $table) {
            $table->uuid('liga_id')->nullable()->after('usuario_id');
            $table->index(['liga_id', 'partido_id']);
        });
    }

    public function down(): void
    {
        Schema::table('predicciones', function (Blueprint $table) {
            $table->dropIndex(['liga_id', 'partido_id']);
            $table->dropColumn('liga_id');
        });
    }
};
