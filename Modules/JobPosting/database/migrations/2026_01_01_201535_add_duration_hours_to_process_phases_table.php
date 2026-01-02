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
        Schema::table('process_phases', function (Blueprint $table) {
            $table->integer('default_duration_hours')->nullable()->after('default_duration_days')
                ->comment('Duración por defecto en horas (para fases cortas)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_phases', function (Blueprint $table) {
            $table->dropColumn('default_duration_hours');
        });
    }
};
