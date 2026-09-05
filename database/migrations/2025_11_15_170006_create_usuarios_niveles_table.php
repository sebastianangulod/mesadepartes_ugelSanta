<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios_niveles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedTinyInteger('nivel')->comment('1, 2, 3, 4');
            $table->unsignedBigInteger('id_nivel');
            $table->timestamps();

            $table->unique(['id_usuario', 'nivel'], 'usuarios_niveles_usuario_nivel_unique');
            $table->foreign('id_usuario')->references('id')->on('usuarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_niveles');
    }
};