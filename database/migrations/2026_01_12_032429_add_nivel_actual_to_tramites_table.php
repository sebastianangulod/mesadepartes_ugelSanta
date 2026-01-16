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
        Schema::table('tramites', function (Blueprint $table) {
            $table->tinyInteger('nivel_actual')->nullable()->after('estado')->comment('Nivel actual del trámite (1, 2, 3 o 4)');
            $table->index('nivel_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->dropIndex(['nivel_actual']);
            $table->dropColumn('nivel_actual');
        });
    }
};
