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
        Schema::table('evaluation_criteria', function (Blueprint $table) {
            // Agregar position_code_id para filtrar criterios por código de puesto
            $table->foreignUuid('position_code_id')
                ->nullable()
                ->after('job_posting_id')
                ->constrained('position_codes')
                ->onDelete('cascade')
                ->comment('Código de puesto específico al que aplica este criterio');

            // Índice para mejorar las consultas
            $table->index('position_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_criteria', function (Blueprint $table) {
            $table->dropForeign(['position_code_id']);
            $table->dropIndex(['position_code_id']);
            $table->dropColumn('position_code_id');
        });
    }
};
