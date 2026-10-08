<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar unique constraints simples si existen (de migración anterior)
        foreach (['nivel_1', 'nivel_2', 'nivel_3', 'nivel_4'] as $tabla) {
            try {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropUnique([$tabla . '_nombre_unique']));
            } catch (\Throwable) {
                // No existía, continuar
            }
        }

        // 2. Limpiar duplicados REALES (mismo padre + mismo nombre)
        // nivel_2: duplicados = mismo id_nivel1 + mismo nombre
        DB::statement("
            DELETE t1 FROM nivel_2 t1
            INNER JOIN nivel_2 t2
            WHERE t1.id > t2.id 
            AND t1.id_nivel1 = t2.id_nivel1 
            AND t1.nombre = t2.nombre
        ");

        // nivel_3: duplicados = mismo id_nivel2 + mismo nombre
        DB::statement("
            DELETE t1 FROM nivel_3 t1
            INNER JOIN nivel_3 t2
            WHERE t1.id > t2.id 
            AND t1.id_nivel2 = t2.id_nivel2 
            AND t1.nombre = t2.nombre
        ");

        // nivel_4: duplicados = mismo id_nivel3 + mismo nombre
        DB::statement("
            DELETE t1 FROM nivel_4 t1
            INNER JOIN nivel_4 t2
            WHERE t1.id > t2.id 
            AND t1.id_nivel3 = t2.id_nivel3 
            AND t1.nombre = t2.nombre
        ");

        // 3. Agregar unique constraints COMPUESTOS
        Schema::table('nivel_1', fn (Blueprint $t) => $t->unique('nombre', 'nivel_1_nombre_unique'));
        Schema::table('nivel_2', fn (Blueprint $t) => $t->unique(['id_nivel1', 'nombre'], 'nivel_2_parent_nombre_unique'));
        Schema::table('nivel_3', fn (Blueprint $t) => $t->unique(['id_nivel2', 'nombre'], 'nivel_3_parent_nombre_unique'));
        Schema::table('nivel_4', fn (Blueprint $t) => $t->unique(['id_nivel3', 'nombre'], 'nivel_4_parent_nombre_unique'));
    }

    public function down(): void
    {
        Schema::table('nivel_1', fn (Blueprint $t) => $t->dropUnique('nivel_1_nombre_unique'));
        Schema::table('nivel_2', fn (Blueprint $t) => $t->dropUnique('nivel_2_parent_nombre_unique'));
        Schema::table('nivel_3', fn (Blueprint $t) => $t->dropUnique('nivel_3_parent_nombre_unique'));
        Schema::table('nivel_4', fn (Blueprint $t) => $t->dropUnique('nivel_4_parent_nombre_unique'));
    }
};