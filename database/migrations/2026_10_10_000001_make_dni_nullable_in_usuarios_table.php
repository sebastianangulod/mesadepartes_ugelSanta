<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permite cuentas de Entidad Pública sin DNI:
 * el DNI pasa a ser nullable (el UNIQUE se conserva;
 * MySQL admite múltiples NULL en índice único).
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración
     */
    public function up(): void
    {
        if (Schema::hasColumn('usuarios', 'dni')) {
            // Sin doctrine/dbal: ALTER directo (MySQL).
            DB::statement('ALTER TABLE `usuarios` MODIFY `dni` VARCHAR(15) NULL');
        }
    }

    /**
     * Revertir la migración
     */
    public function down(): void
    {
        if (Schema::hasColumn('usuarios', 'dni')) {
            DB::statement('ALTER TABLE `usuarios` MODIFY `dni` VARCHAR(15) NOT NULL');
        }
    }
};
