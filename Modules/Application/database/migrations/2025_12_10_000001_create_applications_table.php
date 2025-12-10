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
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique()->comment('APP-2025-001-001');

            // Relaciones principales
            $table->foreignUuid('job_profile_vacancy_id')->constrained('job_profile_vacancies')->onDelete('restrict');
            $table->foreignUuid('applicant_id')->constrained('users')->onDelete('restrict');

            // Estado de la postulación
            $table->string('status', 50)->default('PRESENTADA');
            $table->timestamp('application_date')->useCurrent();
            $table->boolean('terms_accepted')->default(false);

            // Datos personales (replicados para snapshot)
            $table->string('full_name');
            $table->string('dni', 8);
            $table->date('birth_date');
            $table->string('address');
            $table->string('phone', 20)->nullable();
            $table->string('mobile_phone', 20);
            $table->string('email');

            // Elegibilidad
            $table->boolean('is_eligible')->nullable();
            $table->foreignUuid('eligibility_checked_by')->nullable()->constrained('users');
            $table->timestamp('eligibility_checked_at')->nullable();
            $table->text('ineligibility_reason')->nullable();

            // Subsanación
            $table->boolean('requires_amendment')->default(false);
            $table->date('amendment_deadline')->nullable();
            $table->text('amendment_notes')->nullable();

            // Puntajes
            $table->decimal('curriculum_score', 5, 2)->nullable();
            $table->decimal('interview_score', 5, 2)->nullable();
            $table->decimal('special_condition_bonus', 5, 2)->default(0);
            $table->decimal('final_score', 5, 2)->nullable();
            $table->integer('final_ranking')->nullable();

            // Metadatos
            $table->string('ip_address', 45)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('code');
            $table->index('status');
            $table->index('dni');
            $table->index(['job_profile_vacancy_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
