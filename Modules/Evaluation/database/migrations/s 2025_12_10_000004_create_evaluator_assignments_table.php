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
        Schema::create('evaluator_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Relaciones
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('phase_id')->constrained('process_phases')->onDelete('restrict');
            $table->foreignId('job_posting_id')->constrained('job_postings')->onDelete('cascade');
            
            // Asignación
            $table->enum('assignment_type', ['MANUAL', 'AUTOMATIC'])->default('MANUAL');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->useCurrent();
            
            // Estado
            $table->enum('status', [
                'PENDING',      // Pendiente
                'IN_PROGRESS',  // En progreso
                'COMPLETED',    // Completada
                'CANCELLED',    // Cancelada
                'REASSIGNED'    // Reasignada
            ])->default('PENDING');
            
            // Control de carga
            $table->integer('workload_weight')->default(1)->comment('Peso de carga de trabajo');
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Conflicto de interés
            $table->boolean('has_conflict')->default(false);
            $table->text('conflict_reason')->nullable();
            
            // Disponibilidad
            $table->boolean('is_available')->default(true);
            $table->text('unavailability_reason')->nullable();
            
            // Notificaciones
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['evaluator_id', 'status']);
            $table->index(['application_id', 'phase_id']);
            $table->index('job_posting_id');
            $table->index('deadline_at');
            
            // Constraint: Una asignación única por evaluador-aplicación-fase
            $table->unique(['evaluator_id', 'application_id', 'phase_id'], 'unique_evaluator_application_phase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluator_assignments');
    }
};