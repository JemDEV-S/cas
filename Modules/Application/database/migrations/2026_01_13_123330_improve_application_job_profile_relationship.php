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
        // 1. Agregar nueva columna job_profile_id
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignUuid('job_profile_id')
                  ->after('code')
                  ->nullable() // Temporal para migración
                  ->constrained('job_profiles')
                  ->onDelete('restrict');
        });

        // 2. Migrar datos existentes (antes de renombrar)
        DB::statement("
            UPDATE applications a
            INNER JOIN job_profile_vacancies v ON a.job_profile_vacancy_id = v.id
            SET a.job_profile_id = v.job_profile_id
        ");

        // 3. Renombrar y hacer nullable la columna
        Schema::table('applications', function (Blueprint $table) {
            $table->renameColumn('job_profile_vacancy_id', 'assigned_vacancy_id');
        });

        // 4. Hacer assigned_vacancy_id nullable
        DB::statement("ALTER TABLE applications MODIFY COLUMN assigned_vacancy_id CHAR(36) NULL");

        // 5. Limpiar assigned_vacancy_id (solo mantener ganadores reales)
        // Si la vacante no tiene assigned_application_id = esta application, entonces limpiar
        DB::statement("
            UPDATE applications a
            LEFT JOIN job_profile_vacancies v ON v.assigned_application_id = a.id
            SET a.assigned_vacancy_id = CASE
                WHEN v.id IS NOT NULL THEN a.assigned_vacancy_id
                ELSE NULL
            END
        ");

        // 6. Hacer job_profile_id NOT NULL
        Schema::table('applications', function (Blueprint $table) {
            $table->uuid('job_profile_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->renameColumn('assigned_vacancy_id', 'job_profile_vacancy_id');
            $table->dropForeign(['job_profile_id']);
            $table->dropColumn('job_profile_id');
        });
    }
};
