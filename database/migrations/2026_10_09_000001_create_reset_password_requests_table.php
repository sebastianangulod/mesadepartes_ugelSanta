<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitudes de restablecimiento de contraseña (flujo por DNI).
 * El modelo y el servicio ya existían; la tabla nunca se creó.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reset_password_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 100)->unique();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reset_password_requests');
    }
};
