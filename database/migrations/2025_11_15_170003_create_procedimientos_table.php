<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla de procedimientos
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('procedimientos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_subarea')->nullable();
            $table->enum('nivel_final', ['2', '3', '4'])->default('4');
            $table->unsignedBigInteger('id_nivel_final')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Índices
            $table->index('id_area');
            $table->index('id_subarea');
            $table->index('id_nivel_final');

            // Claves foráneas
            $table->foreign('id_area')->references('id')->on('nivel_1')->onDelete('set null');
            $table->foreign('id_subarea')->references('id')->on('nivel_2')->onDelete('set null');
            // id_nivel_final SIN foreign key a propósito: puede referenciar nivel_2,
            // nivel_3 o nivel_4 según nivel_final. La integridad se valida en código.
            $table->foreign('created_by')->references('id')->on('usuarios')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('usuarios')->nullOnDelete();
        });
    }

    /**
     * Revertir la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('procedimientos');
    }
};