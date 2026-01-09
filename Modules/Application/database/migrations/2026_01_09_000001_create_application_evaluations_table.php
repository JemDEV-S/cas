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
        Schema::create('application_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')
                ->constrained('applications')
                ->onDelete('cascade');

            // Información de la evaluación
            $table->boolean('is_eligible')->default(false);
            $table->text('ineligibility_reasons')->nullable();

            // Resultados detallados por criterio (JSON)
            $table->json('academics_evaluation')->nullable()
                ->comment('Resultado de evaluación académica');
            $table->json('general_experience_evaluation')->nullable()
                ->comment('Resultado de evaluación de experiencia general');
            $table->json('specific_experience_evaluation')->nullable()
                ->comment('Resultado de evaluación de experiencia específica');
            $table->json('professional_registry_evaluation')->nullable()
                ->comment('Resultado de evaluación de colegiatura');
            $table->json('osce_certification_evaluation')->nullable()
                ->comment('Resultado de evaluación de certificación OSCE');
            $table->json('driver_license_evaluation')->nullable()
                ->comment('Resultado de evaluación de licencia');
            $table->json('required_courses_evaluation')->nullable()
                ->comment('Resultado de evaluación de cursos requeridos');
            $table->json('technical_knowledge_evaluation')->nullable()
                ->comment('Resultado de evaluación de conocimientos técnicos');

            // Metadatos de la evaluación
            $table->string('algorithm_version')->default('1.0')
                ->comment('Versión del algoritmo de evaluación');
            $table->foreignUuid('evaluated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que ejecutó la evaluación');
            $table->timestamp('evaluated_at')->useCurrent();

            $table->timestamps();

            // Índices
            $table->index('is_eligible');
            $table->index('evaluated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_evaluations');
    }
};
