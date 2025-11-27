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
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['ADMINISTRATIVO', 'ACADEMICO', 'LEGAL', 'OTRO'])->default('ADMINISTRATIVO');
            $table->decimal('costo', 10, 2)->default(0.00);
            $table->integer('tiempo_estimado_dias')->default(15);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_nivel')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('codigo');
            $table->index('estado');
            $table->index('id_area');
            $table->index('id_nivel');
            
            // Claves foráneas
            $table->foreign('id_area')->references('id')->on('areas')->onDelete('set null');
            $table->foreign('id_nivel')->references('id')->on('niveles')->onDelete('set null');
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