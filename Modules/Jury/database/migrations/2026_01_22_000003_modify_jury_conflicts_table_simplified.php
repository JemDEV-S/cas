<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Simplifica jury_conflicts según diseño optimizado:
     * - Cambia jury_member_id a user_id (relación directa con users)
     * - Elimina campos de workflow complejo (severity, status, resolution, review)
     * - Solo para conflictos manuales: FAMILY y PERSONAL
     * - Campos mínimos: user_id, application_id, type, description
     */
    public function up(): void
    {
        Schema::table('jury_conflicts', function (Blueprint $table) {
            // Eliminar todas las foreign keys
            $table->dropForeign(['jury_member_id']);
            $table->dropForeign(['application_id']);
            $table->dropForeign(['job_posting_id']);
            $table->dropForeign(['applicant_id']);
            $table->dropForeign(['resolved_by']);
            $table->dropForeign(['reported_by']);
            $table->dropForeign(['reviewed_by']);

            // Eliminar índices
            $table->dropIndex(['jury_member_id']);
            $table->dropIndex(['application_id']);
            $table->dropIndex(['job_posting_id']);
            $table->dropIndex(['applicant_id']);
            $table->dropIndex(['status', 'severity']);
            $table->dropIndex(['conflict_type']);
            $table->dropIndex(['reported_at']);
            $table->dropIndex(['jury_member_id', 'application_id']);
            $table->dropIndex(['jury_member_id', 'job_posting_id']);
        });

        Schema::table('jury_conflicts', function (Blueprint $table) {
            // Renombrar jury_member_id a user_id
            $table->renameColumn('jury_member_id', 'user_id');
            $table->renameColumn('conflict_type', 'type');
        });

        Schema::table('jury_conflicts', function (Blueprint $table) {
            // Eliminar columnas del workflow complejo
            $table->dropColumn([
                'job_posting_id',
                'applicant_id',
                'severity',
                'evidence_path',
                'additional_details',
                'status',
                'resolution',
                'resolved_by',
                'resolved_at',
                'action_taken',
                'action_notes',
                'reported_by',
                'reported_at',
                'is_self_reported',
                'reviewed_by',
                'reviewed_at',
                'review_notes',
                'metadata',
            ]);

            // Modificar type a solo FAMILY y PERSONAL
            $table->enum('type', ['FAMILY', 'PERSONAL'])->change();

            // application_id ahora es obligatorio (no nullable)
            $table->foreignUuid('application_id')->change();

            // Recrear foreign keys simplificadas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');

            // Recrear índices simplificados
            $table->index(['user_id', 'application_id']);
            $table->index('type');

            // Constraint único: un usuario solo puede tener un conflicto por aplicación
            $table->unique(['user_id', 'application_id'], 'unique_user_application_conflict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jury_conflicts', function (Blueprint $table) {
            // Eliminar foreign keys y constraints nuevos
            $table->dropForeign(['user_id']);
            $table->dropForeign(['application_id']);
            $table->dropUnique('unique_user_application_conflict');
            $table->dropIndex(['user_id', 'application_id']);
            $table->dropIndex(['type']);
        });

        Schema::table('jury_conflicts', function (Blueprint $table) {
            // Renombrar de vuelta
            $table->renameColumn('user_id', 'jury_member_id');
            $table->renameColumn('type', 'conflict_type');
        });

        Schema::table('jury_conflicts', function (Blueprint $table) {
            // Hacer application_id nullable nuevamente
            $table->foreignUuid('application_id')->nullable()->change();

            // Restaurar columnas eliminadas
            $table->foreignUuid('job_posting_id')->nullable()->after('application_id')
                ->constrained('job_postings')->onDelete('cascade');
            $table->foreignUuid('applicant_id')->nullable()->after('job_posting_id')
                ->constrained('users')->onDelete('cascade');

            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM')
                ->after('conflict_type');
            $table->string('evidence_path')->nullable()->after('description');
            $table->json('additional_details')->nullable()->after('evidence_path');

            $table->enum('status', ['REPORTED', 'UNDER_REVIEW', 'CONFIRMED', 'DISMISSED', 'RESOLVED'])
                ->default('REPORTED')->after('additional_details');

            $table->text('resolution')->nullable()->after('status');
            $table->foreignUuid('resolved_by')->nullable()->after('resolution')
                ->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');

            $table->enum('action_taken', ['EXCUSED', 'REASSIGNED', 'APPLICANT_REMOVED', 'NO_ACTION', 'PENDING', 'OTHER'])
                ->nullable()->after('resolved_at');
            $table->text('action_notes')->nullable()->after('action_taken');

            $table->foreignUuid('reported_by')->nullable()->after('action_notes')
                ->constrained('users')->onDelete('set null');
            $table->timestamp('reported_at')->useCurrent()->after('reported_by');
            $table->boolean('is_self_reported')->default(false)->after('reported_at');

            $table->foreignUuid('reviewed_by')->nullable()->after('is_self_reported')
                ->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');

            $table->json('metadata')->nullable()->after('review_notes');

            // Restaurar enum conflict_type original
            $table->enum('conflict_type', [
                'FAMILY', 'LABOR', 'PROFESSIONAL', 'FINANCIAL', 'PERSONAL',
                'ACADEMIC', 'PRIOR_EVALUATION', 'OTHER'
            ])->change();

            // Restaurar foreign keys
            $table->foreign('jury_member_id')->references('id')->on('jury_members')->onDelete('cascade');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');

            // Restaurar índices
            $table->index('jury_member_id');
            $table->index('application_id');
            $table->index('job_posting_id');
            $table->index('applicant_id');
            $table->index(['status', 'severity']);
            $table->index('conflict_type');
            $table->index('reported_at');
            $table->index(['jury_member_id', 'application_id']);
            $table->index(['jury_member_id', 'job_posting_id']);
        });
    }
};
