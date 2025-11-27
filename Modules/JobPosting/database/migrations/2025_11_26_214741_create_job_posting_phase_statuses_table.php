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
            // Primary Key
            $table->uuid('id')->primary();
            
            // Relaciones
            $table->uuid('job_posting_id')->comment('ID de la convocatoria');
            $table->uuid('job_posting_schedule_id')->comment('ID del cronograma de fase');
            
            // Estado detallado
            $table->string('status', 50)->comment('Estado: PENDING, IN_PROGRESS, COMPLETED, DELAYED, CANCELLED');
            $table->integer('progress_percentage')->default(0)->comment('Porcentaje de avance (0-100)');
            
            // Fechas reales
            $table->timestamp('started_at')->nullable()->comment('Fecha real de inicio');
            $table->timestamp('completed_at')->nullable()->comment('Fecha real de completado');
            
            // Responsables
            $table->uuid('started_by')->nullable()->comment('Usuario que inició la fase');
            $table->uuid('completed_by')->nullable()->comment('Usuario que completó la fase');
            
            // Observaciones
            $table->text('observations')->nullable()->comment('Observaciones durante la ejecución');
            $table->json('checklist')->nullable()->comment('Lista de verificación de la fase');
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->onDelete('cascade');
            $table->foreign('job_posting_schedule_id')->references('id')->on('job_posting_schedules')->onDelete('cascade');
            $table->foreign('started_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('job_posting_id');
            $table->index('job_posting_schedule_id');
            $table->index('status');
            $table->index(['job_posting_id', 'status']);
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