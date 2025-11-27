<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_posting_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relaciones
            $table->uuid('job_posting_id')->comment('Convocatoria a la que pertenece');
            $table->uuid('process_phase_id')->comment('Fase del proceso');
            $table->uuid('responsible_unit_id')->nullable()->comment('Unidad organizacional responsable');

            // Fechas programadas
            $table->date('scheduled_start_date')->comment('Fecha de inicio programada');
            $table->date('scheduled_end_date')->comment('Fecha de fin programada');

            // Fechas reales
            $table->date('actual_start_date')->nullable()->comment('Fecha de inicio real');
            $table->date('actual_end_date')->nullable()->comment('Fecha de fin real');

            // Detalles
            $table->string('location')->nullable()->comment('Ubicación de la fase (ej: Portal Web, Municipalidad)');
            $table->text('notes')->nullable()->comment('Notas o detalles adicionales');

            // Estado de la fase
            $table->string('status', 30)->default('PENDING')->comment('Estado: PENDING, IN_PROGRESS, COMPLETED, DELAYED');

            // Orden
            $table->integer('order')->comment('Orden de ejecución de la fase');

            // Metadata
            $table->jsonb('metadata')->nullable()->comment('Información adicional');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('job_posting_id');
            $table->index('process_phase_id');
            $table->index('responsible_unit_id');
            $table->index('status');
            $table->index('scheduled_start_date');
            $table->index('scheduled_end_date');
            $table->index('actual_start_date');
            $table->index('actual_end_date');
            $table->index(['job_posting_id', 'order']);
            $table->index(['job_posting_id', 'status']);

            // Foreign Keys
            $table->foreign('job_posting_id')
                ->references('id')
                ->on('job_postings')
                ->onDelete('cascade');

            $table->foreign('process_phase_id')
                ->references('id')
                ->on('process_phases')
                ->onDelete('restrict');

            $table->foreign('responsible_unit_id')
                ->references('id')
                ->on('organizational_units')
                ->onDelete('set null');
        });
        DB::statement("
            ALTER TABLE job_posting_schedules
            ADD CONSTRAINT chk_scheduled_dates
            CHECK (scheduled_start_date <= scheduled_end_date)
        ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posting_schedules');
    }
};
