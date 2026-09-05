<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedimiento_usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_procedimiento');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();

            $table->unique(['id_procedimiento', 'id_usuario'], 'procedimiento_usuarios_proc_user_unique');
            $table->foreign('id_procedimiento')->references('id')->on('procedimientos')->cascadeOnDelete();
            $table->foreign('id_usuario')->references('id')->on('usuarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedimiento_usuarios');
    }
};