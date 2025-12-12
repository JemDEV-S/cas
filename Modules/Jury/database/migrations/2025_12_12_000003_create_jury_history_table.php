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
        Schema::create('jury_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relaciones (al menos una debe existir)
            $table->foreignUuid('jury_assignment_id')->nullable()->constrained('jury_assignments')->onDelete('cascade')
                ->comment('Asignación relacionada');
            $table->foreignUuid('jury_member_id')->nullable()->constrained('jury_members')->onDelete('cascade')
                ->comment('Jurado relacionado');
            $table->foreignUuid('job_posting_id')->nullable()->constrained('job_postings')->onDelete('cascade')
                ->comment('Convocatoria relacionada');
            
            // Tipo de evento
            $table->string('event_type', 50)->comment('ASSIGNED, REPLACED, EXCUSED, REMOVED, SUSPENDED, TRAINING_COMPLETED, CONFLICT_REPORTED, CONFLICT_RESOLVED, WORKLOAD_UPDATED, STATUS_CHANGED, etc.');
            
            // Descripción del evento
            $table->text('description')->nullable()->comment('Descripción del evento');
            $table->text('reason')->nullable()->comment('Razón del cambio');
            
            // Cambios realizados
            $table->json('old_values')->nullable()->comment('Valores anteriores');
            $table->json('new_values')->nullable()->comment('Valores nuevos');
            
            // Campos específicos para ciertos eventos
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->uuid('related_jury_member_id')->nullable()->comment('Jurado relacionado (para reemplazos)');
            
            // Auditoría
            $table->foreignUuid('performed_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Usuario que realizó la acción');
            $table->timestamp('performed_at')->useCurrent()->comment('Fecha y hora del evento');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Metadata adicional
            $table->json('metadata')->nullable();
            
            // No tiene timestamps ya que performed_at es el timestamp principal
            
            // Índices
            $table->index('jury_assignment_id');
            $table->index('jury_member_id');
            $table->index('job_posting_id');
            $table->index('event_type');
            $table->index('performed_by');
            $table->index('performed_at');
            $table->index(['jury_member_id', 'event_type']);
            $table->index(['job_posting_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jury_history');
    }
};