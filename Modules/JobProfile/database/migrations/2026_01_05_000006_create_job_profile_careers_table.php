<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabla pivote que relaciona JobProfiles con AcademicCareers.
     * Esta tabla elimina la necesidad de parsear career_field en cada validación.
     */
    public function up(): void
    {
        Schema::create('job_profile_careers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_profile_id')->comment('FK a job_profiles');
            $table->uuid('career_id')->comment('FK a academic_careers');
            $table->boolean('is_primary')->default(false)->comment('Carrera principal/preferida');
            $table->string('mapping_source', 50)->default('MANUAL')->comment('AUTO, MANUAL, MIGRATION');
            $table->string('mapped_from_text', 255)->nullable()->comment('Texto original del career_field');
            $table->decimal('confidence_score', 5, 2)->nullable()->comment('Score de confianza si fue auto-mapeado (0-100)');
            $table->timestamps();

            // Foreign keys
            $table->foreign('job_profile_id')
                ->references('id')
                ->on('job_profiles')
                ->onDelete('cascade');

            $table->foreign('career_id')
                ->references('id')
                ->on('academic_careers')
                ->onDelete('cascade');

            // Índices y constraints
            $table->unique(['job_profile_id', 'career_id'], 'unique_profile_career');
            $table->index('job_profile_id');
            $table->index('career_id');
            $table->index('is_primary');
            $table->index('mapping_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_profile_careers');
    }
};
