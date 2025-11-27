<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_profile_vacancies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_profile_id');
            $table->integer('vacancy_number')->comment('Número correlativo de la vacante');
            $table->string('code')->unique()->comment('Código autogenerado: CONV-2025-001-01-V01');
            $table->string('status', 50)->default('available')->comment('available, in_process, filled, vacant');

            // Asignación
            $table->uuid('assigned_application_id')->nullable()->comment('Postulación asignada');

            // Declaración desierta
            $table->timestamp('declared_vacant_at')->nullable();
            $table->text('declared_vacant_reason')->nullable();

            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('job_profile_id')->references('id')->on('job_profiles')->cascadeOnDelete();
            // La tabla applications está en el módulo Application
            // $table->foreign('assigned_application_id')->references('id')->on('applications')->nullOnDelete();

            $table->index('job_profile_id');
            $table->index('code');
            $table->index('status');
            $table->index('vacancy_number');
            $table->index('assigned_application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_profile_vacancies');
    }
};
