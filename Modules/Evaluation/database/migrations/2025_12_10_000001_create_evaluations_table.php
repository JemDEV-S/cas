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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Relaciones
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('phase_id')->constrained('process_phases')->onDelete('restrict');
            $table->foreignId('job_posting_id')->constrained('job_postings')->onDelete('cascade');
            
            // Datos de evaluación
            $table->enum('status', [
                'ASSIGNED',      // Asignada
                'IN_PROGRESS',   // En progreso (borrador)
                'SUBMITTED',     // Enviada
                'MODIFIED',      // Modificada (después de envío)
                'CANCELLED'      // Cancelada
            ])->default('ASSIGNED');
            
            $table->decimal('total_score', 8, 2)->nullable()->comment('Puntaje total calculado');
            $table->decimal('max_possible_score', 8, 2)->nullable()->comment('Puntaje máximo posible');
            $table->decimal('percentage', 8, 2)->nullable()->comment('Porcentaje obtenido');
            
            // Datos de control
            $table->timestamp('submitted_at')->nullable()->comment('Fecha de envío');
            $table->timestamp('deadline_at')->nullable()->comment('Fecha límite');
            $table->boolean('is_anonymous')->default(false)->comment('Evaluación anónima');
            $table->boolean('is_collaborative')->default(false)->comment('Evaluación colaborativa');
            
            // Comentarios generales
            $table->text('general_comments')->nullable();
            $table->text('internal_notes')->nullable()->comment('Notas internas (no visibles para postulante)');
            
            // Modificaciones
            $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('modified_at')->nullable();
            $table->text('modification_reason')->nullable();
            
            // Metadata y auditoría
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['application_id', 'phase_id']);
            $table->index('evaluator_id');
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};