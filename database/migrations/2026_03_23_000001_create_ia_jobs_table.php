<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->uuid('job_profile_id');

            // Datos para el LLM
            $table->string('applicant_career')->comment('Carrera del postulante');
            $table->text('required_careers')->comment('career_field del perfil (texto directo)');
            $table->string('applicant_degree_type')->nullable();

            // Estado del job
            $table->enum('status', ['pendiente', 'procesando', 'completado', 'error'])
                ->default('pendiente')
                ->index();

            // Resultado del LLM
            $table->string('resultado')->nullable()->comment('cumple_exacto|cumple_equivalente|cumple_afin|no_cumple|indeterminado');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('justificacion')->nullable();

            // Control
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->foreign('application_id')
                ->references('id')
                ->on('applications')
                ->onDelete('cascade');

            $table->foreign('job_profile_id')
                ->references('id')
                ->on('job_profiles')
                ->onDelete('cascade');

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_jobs');
    }
};
