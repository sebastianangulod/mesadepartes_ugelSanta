<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->unsignedBigInteger('creado_por')->nullable()->after('id_usuario');

            $table->index('creado_por');
            $table->foreign('creado_por')
                ->references('id')
                ->on('usuarios')
                ->onDelete('set null');
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->dropIndex(['creado_por']);
            $table->dropColumn('creado_por');
        });
    }
};

