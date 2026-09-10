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
        Schema::table('nivel_1', function (Blueprint $table) {
            $table->unsignedSmallInteger('orden')->default(0)->after('nombre');
            $table->index('orden');
        });

        Schema::table('nivel_2', function (Blueprint $table) {
            $table->unsignedSmallInteger('orden')->default(0)->after('id_nivel1');
            $table->index('orden');
        });

        Schema::table('nivel_3', function (Blueprint $table) {
            $table->unsignedSmallInteger('orden')->default(0)->after('id_nivel2');
            $table->index('orden');
        });

        Schema::table('nivel_4', function (Blueprint $table) {
            $table->unsignedSmallInteger('orden')->default(0)->after('id_nivel3');
            $table->index('orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nivel_1', function (Blueprint $table) {
            $table->dropIndex(['orden']);
            $table->dropColumn('orden');
        });

        Schema::table('nivel_2', function (Blueprint $table) {
            $table->dropIndex(['orden']);
            $table->dropColumn('orden');
        });

        Schema::table('nivel_3', function (Blueprint $table) {
            $table->dropIndex(['orden']);
            $table->dropColumn('orden');
        });

        Schema::table('nivel_4', function (Blueprint $table) {
            $table->dropIndex(['orden']);
            $table->dropColumn('orden');
        });
    }
};