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
        Schema::create('procedimientos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('tipo', 50)->default('ADMINISTRATIVO');
            $table->integer('dias_tiempo_limite')->default(30);
            $table->decimal('costo', 10, 2)->default(0.00);
            $table->string('moneda', 3)->default('PEN');
            $table->boolean('requiere_digital')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices para búsquedas
            $table->index('codigo');
            $table->index('nombre');
            $table->index('tipo');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedimientos');
    }
};