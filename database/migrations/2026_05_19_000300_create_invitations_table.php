<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('liga_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            // Se guarda solo el hash sha256 del token; el token crudo viaja por mail.
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->foreign('liga_id')->references('id')->on('ligas')->cascadeOnDelete();
            $table->index('liga_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
