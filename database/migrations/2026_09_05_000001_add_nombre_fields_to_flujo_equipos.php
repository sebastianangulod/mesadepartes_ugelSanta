<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flujo_equipos', function (Blueprint $table) {
            $table->string('nombre_tecnico', 150)->nullable()->after('nombre')->comment('Nombre técnico para lógica del sistema (ej: Nivel 1 - AGI)');
            $table->string('nombre_visible', 200)->nullable()->after('nombre_tecnico')->comment('Nombre visible para UI (ej: Secretaría de AGI)');
            
            // Renombrar columna actual 'nombre' a 'nombre_tecnico' manteniendo datos
            // (MySQL no permite renombrar y agregar en mismo statement, hacerlo en 2 pasos si hay datos)
        });
        
        // Migrar datos existentes: nombre -> nombre_tecnico
        DB::statement('UPDATE flujo_equipos SET nombre_tecnico = nombre WHERE nombre_tecnico IS NULL');
    }

    public function down(): void
    {
        Schema::table('flujo_equipos', function (Blueprint $table) {
            $table->dropColumn(['nombre_tecnico', 'nombre_visible']);
        });
    }
};