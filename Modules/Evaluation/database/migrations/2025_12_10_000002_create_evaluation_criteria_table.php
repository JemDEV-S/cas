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
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Relaciones
            $table->foreignId('phase_id')->constrained('process_phases')->onDelete('cascade');
            $table->foreignId('job_posting_id')->nullable()->constrained('job_postings')->onDelete('cascade')
                ->comment('Si es específico de una convocatoria');
            
            // Datos del criterio
            $table->string('code', 50)->unique()->comment('Código único del criterio');
            $table->string('name')->comment('Nombre del criterio');
            $table->text('description')->nullable();
            
            // Configuración de puntaje
            $table->decimal('min_score', 8, 2)->default(0)->comment('Puntaje mínimo');
            $table->decimal('max_score', 8, 2)->comment('Puntaje máximo');
            $table->decimal('weight', 8, 2)->default(1)->comment('Peso/ponderación del criterio');
            $table->integer('order')->default(0)->comment('Orden de visualización');
            
            // Configuración
            $table->boolean('requires_comment')->default(false)->comment('Requiere comentario obligatorio');
            $table->boolean('requires_evidence')->default(false)->comment('Requiere evidencia/justificación');
            $table->enum('score_type', ['NUMERIC', 'PERCENTAGE', 'QUALITATIVE'])->default('NUMERIC');
            
            // Criterios de calificación (escalas)
            $table->json('score_scales')->nullable()->comment('Escalas de calificación predefinidas');
            $table->text('evaluation_guide')->nullable()->comment('Guía de evaluación');
            
            // Control
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment('Criterio del sistema (protegido)');
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['phase_id', 'is_active']);
            $table->index('job_posting_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria');
    }
};