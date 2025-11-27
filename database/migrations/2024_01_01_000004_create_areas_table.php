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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion', 200)->nullable();
            $table->integer('nivel')->default(1);
            $table->unsignedBigInteger('area_padre_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Relación con área padre
            $table->foreign('area_padre_id')
                ->references('id')
                ->on('areas')
                ->onDelete('cascade');
                
            // Índices para búsquedas
            $table->index('codigo');
            $table->index('nombre');
            $table->index('nivel');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};