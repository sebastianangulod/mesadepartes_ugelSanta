<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bandeja de notificaciones por usuario.
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->string('titulo', 120);
            $table->text('mensaje')->nullable();
            // info | success | warning | error
            $table->string('tipo', 20)->default('info');
            $table->string('url', 500)->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->index(['id_usuario', 'leida']);
        });
    }

    /**
     * Revertir la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
