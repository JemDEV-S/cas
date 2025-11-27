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
        Schema::create('process_phases', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identificación
            $table->string('code', 50)->unique()->comment('Código único de la fase (PHASE_01, PHASE_02, etc.)');
            $table->string('name')->comment('Nombre de la fase');
            $table->text('description')->nullable()->comment('Descripción de la fase');

            // Configuración
            $table->integer('phase_number')->comment('Número de orden de la fase (1, 2, 3...)');
            $table->boolean('requires_evaluation')->default(false)->comment('Si la fase requiere evaluación');
            $table->boolean('is_active')->default(true)->comment('Si la fase está activa');
            $table->boolean('is_system')->default(false)->comment('Si es una fase predefinida del sistema (no editable)');

            // Metadata
            $table->jsonb('metadata')->nullable()->comment('Configuración adicional de la fase');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('code');
            $table->index('phase_number');
            $table->index('is_active');
            $table->index('is_system');
            $table->index('requires_evaluation');
            $table->unique(['phase_number', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_phases');
    }
};
