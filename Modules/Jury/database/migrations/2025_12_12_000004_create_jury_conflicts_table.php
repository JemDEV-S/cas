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
        Schema::create('jury_conflicts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relaciones principales
            $table->foreignUuid('jury_member_id')->constrained('jury_members')->onDelete('cascade')
                ->comment('Jurado que tiene el conflicto');
            
            // Contexto del conflicto (al menos uno debe existir)
            $table->foreignUuid('application_id')->nullable()->constrained('applications')->onDelete('cascade')
                ->comment('Postulación específica con conflicto');
            $table->foreignUuid('job_posting_id')->nullable()->constrained('job_postings')->onDelete('cascade')
                ->comment('Convocatoria general con conflicto');
            $table->foreignUuid('applicant_id')->nullable()->constrained('users')->onDelete('cascade')
                ->comment('Postulante con quien tiene conflicto');
            
            // Tipo y severidad del conflicto
            $table->enum('conflict_type', [
                'FAMILY',           // Familiar directo
                'LABOR',            // Relación laboral actual
                'PROFESSIONAL',     // Relación profesional
                'FINANCIAL',        // Interés financiero
                'PERSONAL',         // Amistad o enemistad
                'ACADEMIC',         // Relación académica (asesor/asesorado)
                'PRIOR_EVALUATION', // Evaluó previamente al postulante
                'OTHER'             // Otro tipo
            ])->comment('Tipo de conflicto de interés');
            
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM')
                ->comment('Severidad del conflicto');
            
            // Descripción del conflicto
            $table->text('description')->comment('Descripción detallada del conflicto');
            $table->string('evidence_path')->nullable()->comment('Ruta a evidencia documental');
            $table->json('additional_details')->nullable()->comment('Detalles adicionales');
            
            // Estado del conflicto
            $table->enum('status', [
                'REPORTED',      // Reportado
                'UNDER_REVIEW',  // En revisión
                'CONFIRMED',     // Confirmado
                'DISMISSED',     // Desestimado
                'RESOLVED'       // Resuelto
            ])->default('REPORTED');
            
            // Resolución
            $table->text('resolution')->nullable()->comment('Descripción de la resolución');
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Usuario que resolvió');
            $table->timestamp('resolved_at')->nullable();
            
            // Acción tomada
            $table->enum('action_taken', [
                'EXCUSED',           // Excusado de la evaluación
                'REASSIGNED',        // Reasignado a otro jurado
                'APPLICANT_REMOVED', // Postulante removido (caso extremo)
                'NO_ACTION',         // No se tomó acción (conflicto desestimado)
                'PENDING',           // Pendiente de acción
                'OTHER'
            ])->nullable();
            
            $table->text('action_notes')->nullable()->comment('Notas sobre la acción tomada');
            
            // Reporte
            $table->foreignUuid('reported_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Usuario que reportó el conflicto');
            $table->timestamp('reported_at')->useCurrent()->comment('Fecha del reporte');
            $table->boolean('is_self_reported')->default(false)
                ->comment('El propio jurado reportó el conflicto');
            
            // Revisión
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('jury_member_id');
            $table->index('application_id');
            $table->index('job_posting_id');
            $table->index('applicant_id');
            $table->index(['status', 'severity']);
            $table->index('conflict_type');
            $table->index('reported_at');
            $table->index(['jury_member_id', 'application_id']);
            $table->index(['jury_member_id', 'job_posting_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jury_conflicts');
    }
};