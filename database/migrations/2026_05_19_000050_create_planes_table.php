<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->string('id', 1)->primary();
            $table->string('name');
            $table->unsignedInteger('limite_usuarios')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('planes')->insert([
            ['id' => 'A', 'name' => 'Gratuito', 'limite_usuarios' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'B', 'name' => 'Plan B', 'limite_usuarios' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'C', 'name' => 'Plan C', 'limite_usuarios' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'D', 'name' => 'Plan D', 'limite_usuarios' => 120, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'E', 'name' => 'Sin limite', 'limite_usuarios' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('ligas', function (Blueprint $table) {
            $table->string('plan_id', 1)->default('E')->after('is_active');
            $table->index('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('ligas', function (Blueprint $table) {
            $table->dropIndex(['plan_id']);
            $table->dropColumn('plan_id');
        });

        Schema::dropIfExists('planes');
    }
};
