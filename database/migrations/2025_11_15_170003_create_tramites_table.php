<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla de trámites
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('tramites', function (Blueprint $table) {
            $table->id();
            $table->string('numero_expediente', 50)->unique();
            $table->string('asunto', 255);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'OBSERVADO', 'VERIFICADO', 'FINALIZADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_procedimiento')->nullable();
            $table->string('archivo_expediente', 500)->nullable();
            $table->string('archivo_voucher', 500)->nullable();
            $table->dateTime('fecha_presentacion')->nullable();
            $table->dateTime('fecha_finalizacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->dateTime('fecha_revision')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('numero_expediente');
            $table->index('estado');
            $table->index('id_usuario');
            $table->index('id_procedimiento');
            $table->index('fecha_presentacion');
            $table->index('created_at');
            
            // Claves foráneas
            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_procedimiento')->references('id')->on('procedimientos')->onDelete('set null');
            $table->foreign('revisado_por')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Revertir la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('tramites');
    }
};