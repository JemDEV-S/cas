<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('position_code_id')->comment('Código de posición al que pertenece');
            $table->uuid('process_phase_id')->comment('Fase del proceso (Curricular, Entrevista, etc)');

            $table->string('name')->comment('Nombre del criterio');
            $table->text('description')->nullable();
            $table->decimal('min_score', 5, 2)->default(0)->comment('Puntaje mínimo');
            $table->decimal('max_score', 5, 2)->comment('Puntaje máximo');
            $table->decimal('weight', 5, 2)->comment('Peso/porcentaje (debe sumar 100 por fase)');
            $table->integer('order')->default(0)->comment('Orden de visualización');
            $table->boolean('is_required')->default(true)->comment('Si es obligatorio evaluar');

            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('position_code_id')->references('id')->on('position_codes')->cascadeOnDelete();
            // La tabla process_phases está en el módulo JobPosting
            // $table->foreign('process_phase_id')->references('id')->on('process_phases')->cascadeOnDelete();

            $table->index('position_code_id');
            $table->index('process_phase_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria');
    }
};
