<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear las tablas de niveles jerárquicos (1-4)
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración
     */
    public function up(): void
    {
        // Nivel 1 (Áreas principales)
        Schema::create('nivel_1', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->timestamps();
        });

        // Nivel 2 (Subáreas)
        Schema::create('nivel_2', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->unsignedBigInteger('id_nivel1');
            $table->timestamps();

            $table->foreign('id_nivel1')->references('id')->on('nivel_1')->cascadeOnDelete();
            $table->index('id_nivel1');
        });

        // Nivel 3
        Schema::create('nivel_3', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->unsignedBigInteger('id_nivel2');
            $table->timestamps();

            $table->foreign('id_nivel2')->references('id')->on('nivel_2')->cascadeOnDelete();
            $table->index('id_nivel2');
        });

        // Nivel 4
        Schema::create('nivel_4', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->unsignedBigInteger('id_nivel3');
            $table->timestamps();

            $table->foreign('id_nivel3')->references('id')->on('nivel_3')->cascadeOnDelete();
            $table->index('id_nivel3');
        });
    }

    /**
     * Revertir la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_4');
        Schema::dropIfExists('nivel_3');
        Schema::dropIfExists('nivel_2');
        Schema::dropIfExists('nivel_1');
    }
};