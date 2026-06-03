<?php

use Carbon\Carbon;
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
        if (! Schema::hasColumn('partidos', 'fecha_caracas')) {
            Schema::table('partidos', function (Blueprint $table): void {
                $table->dateTime('fecha_caracas')->nullable()->after('fecha_utc');
            });
        }

        DB::table('partidos')
            ->whereNotNull('fecha_utc')
            ->orderBy('id')
            ->chunkById(100, function ($partidos): void {
                foreach ($partidos as $partido) {
                    DB::table('partidos')
                        ->where('id', $partido->id)
                        ->update([
                            'fecha_caracas' => Carbon::parse($partido->fecha_utc, 'UTC')
                                ->setTimezone('America/Caracas')
                                ->format('Y-m-d H:i:s'),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('partidos', 'fecha_caracas')) {
            Schema::table('partidos', function (Blueprint $table): void {
                $table->dropColumn('fecha_caracas');
            });
        }
    }
};
