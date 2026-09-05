<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flujo configurable por procedimiento: nodos, transiciones, equipos e historial de trámite.
 * Columnas nuevas en tramites/procedimientos (nullable) para convivir con el código existente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flujo_nodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_procedimiento')->constrained('procedimientos')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('slug', 80)->nullable()->comment('Identificador estable opcional para reglas');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('puede_finalizar')->default(false);
            $table->boolean('puede_rechazar_definitivo')->default(false);
            $table->boolean('es_terminal')->default(false)->comment('Nodo de cierre del flujo (referencia UX)');
            $table->timestamps();

            $table->index(['id_procedimiento', 'orden']);
        });

        Schema::create('flujo_transiciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('desde_nodo_id');
            $table->unsignedBigInteger('hacia_nodo_id');
            $table->boolean('es_default_auto')->default(false)->comment('Arista preferida si modo_derivacion = AUTO');
            $table->timestamps();

            $table->foreign('desde_nodo_id')
                ->references('id')
                ->on('flujo_nodos')
                ->cascadeOnDelete();

            $table->foreign('hacia_nodo_id')
                ->references('id')
                ->on('flujo_nodos')
                ->cascadeOnDelete();

            $table->unique(['desde_nodo_id', 'hacia_nodo_id'], 'flujo_transiciones_desde_hacia_unique');
            $table->index('desde_nodo_id');
        });

        Schema::create('flujo_equipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nodo_id');
            $table->string('nombre', 120);
            $table->timestamps();

            $table->foreign('nodo_id')
                ->references('id')
                ->on('flujo_nodos')
                ->cascadeOnDelete();

            $table->index('nodo_id');
        });

        Schema::create('flujo_equipo_usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_id');
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamps();

            $table->foreign('equipo_id')
                ->references('id')
                ->on('flujo_equipos')
                ->cascadeOnDelete();

            $table->unique(['equipo_id', 'usuario_id'], 'flujo_equipo_usuario_equipo_usuario_unique');
            $table->index('usuario_id');
        });

        Schema::create('tramite_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tramite_id')->constrained()->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('accion', 80);
            $table->unsignedBigInteger('nodo_desde_id')->nullable();
            $table->unsignedBigInteger('nodo_hasta_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('nodo_desde_id')
                ->references('id')
                ->on('flujo_nodos')
                ->nullOnDelete();

            $table->foreign('nodo_hasta_id')
                ->references('id')
                ->on('flujo_nodos')
                ->nullOnDelete();

            $table->index(['tramite_id', 'created_at']);
        });

        Schema::table('procedimientos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_nodo_entrada')->nullable()->after('id_nivel_final')->comment('Primer nodo del flujo (admin)');

            $table->foreign('id_nodo_entrada')
                ->references('id')
                ->on('flujo_nodos')
                ->nullOnDelete();
        });

        Schema::table('tramites', function (Blueprint $table) {
            $table->unsignedBigInteger('id_nodo_actual')->nullable()->after('nivel_actual');
            $table->string('modo_derivacion', 20)->default('AUTO')->after('id_nodo_actual');

            $table->foreign('id_nodo_actual')
                ->references('id')
                ->on('flujo_nodos')
                ->nullOnDelete();

            $table->index('modo_derivacion');
        });
    }

    public function down(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->dropForeign(['id_nodo_actual']);
            $table->dropIndex(['modo_derivacion']);
            $table->dropColumn(['id_nodo_actual', 'modo_derivacion']);
        });

        Schema::table('procedimientos', function (Blueprint $table) {
            $table->dropForeign(['id_nodo_entrada']);
            $table->dropColumn('id_nodo_entrada');
        });

        Schema::dropIfExists('tramite_movimientos');
        Schema::dropIfExists('flujo_equipo_usuario');
        Schema::dropIfExists('flujo_equipos');
        Schema::dropIfExists('flujo_transiciones');
        Schema::dropIfExists('flujo_nodos');
    }
};
