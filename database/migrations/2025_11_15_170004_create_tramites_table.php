<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla de trámites.
 *
 * limite_observacion: plazo (habitualmente 2 días) que tiene el ciudadano para subsanar
 * un trámite OBSERVADO. Mesa de Partes alerta los que vencen pronto; al corregir o
 * rechazar definitivamente se limpia (null).
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
            $table->string('numero_expediente', 50)->unique()->nullable();
            $table->string('asunto', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'OBSERVADO', 'VERIFICADO', 'FINALIZADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->string('ultimo_estado', 50)->nullable()->comment('Estado previo al pasar a OBSERVADO; se restaura al subsanar');
            $table->tinyInteger('nivel_actual')->nullable()->comment('Nivel legacy del trámite (1-4)');
            $table->unsignedBigInteger('id_nodo_actual')->nullable()->comment('Nodo del flujo configurable');
            $table->string('modo_derivacion', 20)->default('AUTO');
            $table->unsignedBigInteger('modo_manual_por_usuario')->nullable()
                ->comment('ID del usuario que activó modo manual (null = AUTO para todos)');
            
            $table->foreign('modo_manual_por_usuario')->references('id')->on('usuarios')->onDelete('set null');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('id_procedimiento')->nullable();
            $table->string('archivo_adjunto', 500)->nullable()->comment('PDF principal del trámite');
            $table->string('archivo_expediente', 500)->nullable();
            $table->string('archivo_voucher', 500)->nullable();
            $table->enum('tipo_tramite', ['PRESENCIAL', 'VIRTUAL'])->nullable();
            $table->unsignedSmallInteger('tramitescol')->nullable()->comment('Número de páginas del PDF');
            $table->string('periodo', 4)->nullable();
            $table->dateTime('fecha_presentacion')->nullable();
            $table->dateTime('fecha_finalizacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('limite_observacion')->nullable()->comment('Fecha límite para que el ciudadano subsane observaciones');
            $table->string('oficio', 500)->nullable()->comment('Oficio PDF emitido al observar');
            $table->string('voucher', 500)->nullable();
            $table->string('numero_voucher', 50)->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->unsignedBigInteger('rechazado_por')->nullable();
            $table->dateTime('fecha_rechazo')->nullable();
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->dateTime('fecha_revision')->nullable();
            $table->timestamp('fecha_creacion')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index('numero_expediente');
            $table->index('estado');
            $table->index('nivel_actual');
            $table->index('modo_derivacion');
            $table->index('limite_observacion');
            $table->index('id_usuario');
            $table->index('creado_por');
            $table->index('id_procedimiento');
            $table->index('fecha_presentacion');
            $table->index('fecha_creacion');

            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('creado_por')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('id_procedimiento')->references('id')->on('procedimientos')->onDelete('set null');
            $table->foreign('revisado_por')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('rechazado_por')->references('id')->on('usuarios')->onDelete('set null');
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
