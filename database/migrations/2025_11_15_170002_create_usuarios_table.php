<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla de usuarios
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 20)->nullable()->unique();
            $table->string('razon_social', 200)->nullable();
            $table->string('passw', 255);
            $table->rememberToken();
            $table->string('email', 100)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            // Nullable: las cuentas de Entidad Pública se identifican
            // con el nombre de la entidad y no usan DNI.
            $table->string('dni', 15)->nullable()->unique();
            $table->string('direccion', 255)->nullable();
            $table->unsignedBigInteger('distrito_id')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->unsignedTinyInteger('tipo_user')->default(1)->comment('1: externo, 2: interno');
            $table->unsignedTinyInteger('tipo_persona')->default(1)->comment('1: natural, 2: juridica');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps();

            // Índices
            $table->index('email');
            $table->index('dni');
            $table->index('tipo_user');
            $table->index('distrito_id');
        });
    }

    /**
     * Revertir la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};