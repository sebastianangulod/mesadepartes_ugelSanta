<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('procedimiento_versiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_procedimiento');
            $table->unsignedTinyInteger('version');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_subarea')->nullable();
            $table->enum('nivel_final', ['2', '3', '4'])->default('4');
            $table->unsignedBigInteger('id_nivel_final')->nullable();
            $table->json('flujo_config')->nullable(); // JSON con nodos, transiciones, equipos
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('cambios')->nullable(); // Descripción de cambios
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_procedimiento')->references('id')->on('procedimientos')->onDelete('cascade');
            $table->foreign('id_area')->references('id')->on('nivel_1')->onDelete('set null');
            $table->foreign('id_subarea')->references('id')->on('nivel_2')->onDelete('set null');
            // id_nivel_final SIN foreign key a propósito: puede referenciar nivel_2,
            // nivel_3 o nivel_4 según nivel_final. La integridad se valida en código.
            $table->foreign('created_by')->references('id')->on('usuarios')->onDelete('set null');
            $table->unique(['id_procedimiento', 'version']);
            $table->index(['id_procedimiento', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedimiento_versiones');
    }
};