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
        Schema::create('job_posting_schedules', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();
            
            // Relaciones
            $table->uuid('job_posting_id')->comment('ID de la convocatoria');
            $table->uuid('process_phase_id')->comment('ID de la fase del proceso');
            
            // Fechas programadas
            $table->date('start_date')->comment('Fecha de inicio programada');
            $table->date('end_date')->comment('Fecha de fin programada');
            $table->time('start_time')->nullable()->comment('Hora de inicio (si aplica)');
            $table->time('end_time')->nullable()->comment('Hora de fin (si aplica)');
            
            // Ubicación y responsables
            $table->string('location')->nullable()->comment('Lugar donde se realiza (Portal, Municipalidad, etc.)');
            $table->uuid('responsible_unit_id')->nullable()->comment('Unidad organizacional responsable');
            $table->text('notes')->nullable()->comment('Notas adicionales sobre esta fase');
            
            // Estado de ejecución
            $table->string('status', 50)->default('PENDING')->comment('PENDING, IN_PROGRESS, COMPLETED, CANCELLED');
            $table->timestamp('actual_start_date')->nullable()->comment('Fecha real de inicio');
            $table->timestamp('actual_end_date')->nullable()->comment('Fecha real de finalización');
            
            // Notificaciones
            $table->boolean('notify_before')->default(true)->comment('Enviar notificación previa');
            $table->integer('notify_days_before')->default(3)->comment('Días antes para notificar');
            $table->timestamp('notified_at')->nullable()->comment('Fecha de envío de notificación');
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->onDelete('cascade');
            $table->foreign('process_phase_id')->references('id')->on('process_phases')->onDelete('restrict');
            $table->foreign('responsible_unit_id')->references('id')->on('organizational_units')->onDelete('set null');
            
            // Unique constraint - una fase por convocatoria
            $table->unique(['job_posting_id', 'process_phase_id'], 'unique_phase_per_posting');
            
            // Indexes
            $table->index('job_posting_id');
            $table->index('process_phase_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['job_posting_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posting_schedules');
    }
};