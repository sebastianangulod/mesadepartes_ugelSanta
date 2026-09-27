<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remueve la FK que obliga id_nivel_final a referenciar solo nivel_3.
     * Ahora id_nivel_final puede referenciar nivel_2, nivel_3 o nivel_4
     * según el valor de nivel_final. La integridad se valida en código.
     */
    public function up(): void
    {
        Schema::table('procedimiento_versiones', function (Blueprint $table) {
            $table->dropForeign(['id_nivel_final']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedimiento_versiones', function (Blueprint $table) {
            $table->foreign('id_nivel_final')
                ->references('id')
                ->on('nivel_3')
                ->onDelete('set null');
        });
    }
};
