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
            // Agregamos la columna faltante sin tocar los datos existentes
            $table->boolean('is_system')
                  ->default(false)
                  ->after('requires_evaluation') // Para orden visual
                  ->comment('Indica si es una fase obligatoria del sistema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_phases', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};