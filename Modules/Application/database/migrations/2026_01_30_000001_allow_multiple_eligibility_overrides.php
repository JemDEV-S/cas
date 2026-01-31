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
        Schema::table('eligibility_overrides', function (Blueprint $table) {
            // Eliminar el índice único de application_id para permitir múltiples reclamos
            $table->dropUnique(['application_id']);

            // Agregar índice normal para mantener rendimiento en consultas
            $table->index('application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eligibility_overrides', function (Blueprint $table) {
            // Revertir: eliminar índice normal
            $table->dropIndex(['application_id']);

            // Restaurar índice único
            $table->unique('application_id');
        });
    }
};
