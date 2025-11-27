<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para modificar la tabla de usuarios
 * Adapta la estructura de usuarios de Laravel a la estructura del sistema
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Renombrar la tabla a 'usuarios' para mantener consistencia
            // Esto se hace creando una nueva tabla y migrando los datos
        });
        
        // Crear la tabla usuarios con la estructura necesaria
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 15)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->unsignedBigInteger('id_departamento')->nullable();
            $table->unsignedBigInteger('id_provincia')->nullable();
            $table->unsignedBigInteger('id_distrito')->nullable();
            $table->enum('rol', ['ADMINISTRADOR', 'OPERADOR', 'USUARIO'])->default('USUARIO');
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'])->default('ACTIVO');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Índices
            $table->index('dni');
            $table->index('email');
            $table->index('rol');
            $table->index('estado');
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