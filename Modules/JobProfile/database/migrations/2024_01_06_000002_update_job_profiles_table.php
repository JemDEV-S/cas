<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_profiles', function (Blueprint $table) {
            // Relaciones
            $table->uuid('job_posting_id')->nullable()->after('id');
            $table->uuid('position_code_id')->nullable()->after('organizational_unit_id');
            $table->uuid('requesting_unit_id')->nullable()->after('position_code_id');

            // Información del perfil
            $table->string('profile_name')->nullable()->after('title');

            // Requisitos académicos
            $table->string('education_level', 50)->nullable()->after('description');
            $table->string('career_field')->nullable();
            $table->string('title_required')->nullable();
            $table->boolean('colegiatura_required')->default(false);

            // Experiencia
            $table->decimal('general_experience_years', 3, 1)->nullable();
            $table->decimal('specific_experience_years', 3, 1)->nullable();
            $table->text('specific_experience_description')->nullable();

            // Capacitación, conocimientos, competencias (JSONB)
            $table->jsonb('required_courses')->nullable();
            $table->jsonb('knowledge_areas')->nullable();
            $table->jsonb('required_competencies')->nullable();

            // Funciones del puesto
            $table->jsonb('main_functions')->nullable();

            // Régimen laboral
            $table->string('work_regime', 50)->nullable();
            $table->text('justification')->nullable();

            // Vacantes
            $table->integer('total_vacancies')->default(1);

            // Campos de revisión adicionales
            $table->text('review_comments')->nullable()->after('reviewed_at');
            $table->text('rejection_reason')->nullable()->after('approved_at');

            // Foreign keys
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->nullOnDelete();
            $table->foreign('position_code_id')->references('id')->on('position_codes')->nullOnDelete();
            $table->foreign('requesting_unit_id')->references('id')->on('organizational_units')->nullOnDelete();

            // Índices adicionales
            $table->index('job_posting_id');
            $table->index('position_code_id');
            $table->index('requesting_unit_id');
            $table->index('education_level');
            $table->index('work_regime');
        });
    }

    public function down(): void
    {
        Schema::table('job_profiles', function (Blueprint $table) {
            // Eliminar foreign keys
            $table->dropForeign(['job_posting_id']);
            $table->dropForeign(['position_code_id']);
            $table->dropForeign(['requesting_unit_id']);

            // Eliminar índices
            $table->dropIndex(['job_posting_id']);
            $table->dropIndex(['position_code_id']);
            $table->dropIndex(['requesting_unit_id']);
            $table->dropIndex(['education_level']);
            $table->dropIndex(['work_regime']);

            // Eliminar columnas
            $table->dropColumn([
                'job_posting_id',
                'position_code_id',
                'requesting_unit_id',
                'profile_name',
                'education_level',
                'career_field',
                'title_required',
                'colegiatura_required',
                'general_experience_years',
                'specific_experience_years',
                'specific_experience_description',
                'required_courses',
                'knowledge_areas',
                'required_competencies',
                'main_functions',
                'work_regime',
                'justification',
                'total_vacancies',
                'review_comments',
                'rejection_reason',
            ]);
        });
    }
};
