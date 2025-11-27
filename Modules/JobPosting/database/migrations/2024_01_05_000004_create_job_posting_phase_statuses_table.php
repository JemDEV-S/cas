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
        Schema::create('job_posting_phase_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relaciones
            $table->uuid('job_posting_id')->comment('Convocatoria a la que pertenece');
            $table->uuid('process_phase_id')->comment('Fase del proceso');
            $table->uuid('job_posting_schedule_id')->nullable()->comment('Cronograma relacionado');

            // Estado de la fase en esta convocatoria
            $table->string('status', 30)->default('NOT_STARTED')->comment('Estado: NOT_STARTED, IN_PROGRESS, COMPLETED, SKIPPED');

            // Fechas
            $table->timestamp('started_at')->nullable()->comment('Fecha de inicio');
            $table->timestamp('completed_at')->nullable()->comment('Fecha de finalización');

            // Usuario responsable
            $table->uuid('started_by')->nullable()->comment('Usuario que inició la fase');
            $table->uuid('completed_by')->nullable()->comment('Usuario que completó la fase');

            // Resultados
            $table->text('completion_notes')->nullable()->comment('Notas de finalización');
            $table->jsonb('results')->nullable()->comment('Resultados o métricas de la fase');

            // Metadata
            $table->jsonb('metadata')->nullable()->comment('Información adicional');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('job_posting_id');
            $table->index('process_phase_id');
            $table->index('job_posting_schedule_id');
            $table->index('status');
            $table->index('started_at');
            $table->index('completed_at');
            $table->index(['job_posting_id', 'process_phase_id']);
            $table->index(['job_posting_id', 'status']);

            // Unique: Una fase solo puede tener un estado por convocatoria
            $table->unique(['job_posting_id', 'process_phase_id', 'deleted_at'], 'unique_phase_per_posting');

            // Foreign Keys
            $table->foreign('job_posting_id')
                ->references('id')
                ->on('job_postings')
                ->onDelete('cascade');

            $table->foreign('process_phase_id')
                ->references('id')
                ->on('process_phases')
                ->onDelete('restrict');

            $table->foreign('job_posting_schedule_id')
                ->references('id')
                ->on('job_posting_schedules')
                ->onDelete('set null');

            $table->foreign('started_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('completed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posting_phase_statuses');
    }
};
