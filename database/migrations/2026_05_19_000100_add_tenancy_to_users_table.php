<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Las ligas se aislan por fila: el username/email dejan de ser unicos
        // globales y pasan a ser unicos POR liga. El admin unico global se retira.
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['username']);
            $table->dropUnique(['admin_key']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'admin_key']);
        });

        Schema::table('users', function (Blueprint $table) {
            // liga_id nullable: el superadmin global no pertenece a ninguna liga.
            // Sin FK a nivel DB para mantener portabilidad con SQLite (tests);
            // la integridad la garantizan el observer y el borrado app-level.
            $table->uuid('liga_id')->nullable()->after('id');
            $table->string('role')->default('liga_user')->after('password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['liga_id', 'username']);
            $table->unique(['liga_id', 'email']);
            $table->index('liga_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['liga_id', 'username']);
            $table->dropUnique(['liga_id', 'email']);
            $table->dropIndex(['liga_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['liga_id', 'role']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->string('admin_key')->nullable()->unique()->after('is_admin');
            $table->unique('email');
            $table->unique('username');
        });
    }
};
