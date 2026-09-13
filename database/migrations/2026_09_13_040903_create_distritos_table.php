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
        Schema::create('distritos', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('nombre', 100);
            $table->unsignedInteger('provincia_id');
            $table->foreign('provincia_id')->references('id')->on('provincias')->onDelete('cascade');
            // No timestamps según el modelo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distritos');
    }
};
