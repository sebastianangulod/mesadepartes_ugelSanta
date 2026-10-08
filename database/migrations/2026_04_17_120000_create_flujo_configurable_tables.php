<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flujo por procedimiento: nodos (con su nivel jerárquico) e historial de trámite.
 * Los permisos y el responsable salen directo de usuarios_niveles según el
 * (nivel, id_nivel) de cada nodo. Sin transiciones (AUTO = siguiente por orden)
 * ni equipos (eran espejo 1:1 de usuarios_niveles).
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
            $table->unsignedSmallInteger('orden')->default(0);
            $table->unsignedTinyInteger('nivel')->nullable()->comment('Nivel jerárquico 1-4 del nodo (para permisos vía usuarios_niveles)');
            $table->unsignedBigInteger('id_nivel')->nullable()->comment('ID en nivel_1..4 según nivel (para permisos vía usuarios_niveles)');
            $table->boolean('puede_finalizar')->default(false);
            $table->boolean('puede_rechazar_definitivo')->default(false);
            $table->timestamps();

            $table->index(['id_procedimiento', 'orden']);
            $table->index(['nivel', 'id_nivel']);
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
            $table->foreign('id_nodo_actual')
                ->references('id')
                ->on('flujo_nodos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->dropForeign(['id_nodo_actual']);
        });

        Schema::table('procedimientos', function (Blueprint $table) {
            $table->dropForeign(['id_nodo_entrada']);
            $table->dropColumn('id_nodo_entrada');
        });

        Schema::dropIfExists('tramite_movimientos');
        Schema::dropIfExists('flujo_nodos');
    }
};
