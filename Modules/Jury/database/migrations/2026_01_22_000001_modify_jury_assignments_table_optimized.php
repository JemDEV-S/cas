<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Modifica jury_assignments según diseño optimizado:
     * - Cambia jury_member_id a user_id (relación directa con users)
     * - Elimina campos de workload, reemplazo y excusas
     * - Agrega dependency_scope_id
     * - Simplifica estado a ACTIVE/INACTIVE
     */
    public function up(): void
    {
        Schema::table('jury_assignments', function (Blueprint $table) {
            // Eliminar foreign keys antiguas
            $table->dropForeign(['jury_member_id']);
            $table->dropForeign(['replaced_by']);
            $table->dropForeign(['replacement_approved_by']);
            $table->dropForeign(['excused_by']);

            // Eliminar índices antiguos
            $table->dropIndex(['jury_member_id', 'job_posting_id']);
            $table->dropIndex('unique_jury_assignment');
        });

        Schema::table('jury_assignments', function (Blueprint $table) {
            // Renombrar jury_member_id a user_id
            $table->renameColumn('jury_member_id', 'user_id');
        });

        Schema::table('jury_assignments', function (Blueprint $table) {
            // Eliminar columnas innecesarias
            $table->dropColumn([
                'member_type',
                'order',
                'assignment_resolution',
                'resolution_date',
                'is_active',
                'replaced_by',
                'replacement_reason',
                'replacement_date',
                'replacement_approved_by',
                'excuse_reason',
                'excused_at',
                'excused_by',
                'has_declared_conflicts',
                'conflict_declaration',
                'conflict_declared_at',
                'max_evaluations',
                'current_evaluations',
                'completed_evaluations',
                'available_from',
                'available_until',
                'notified',
                'notified_at',
                'accepted',
                'accepted_at',
            ]);

            // Agregar nueva columna dependency_scope_id
            $table->foreignUuid('dependency_scope_id')->nullable()
                ->after('role_in_jury')
                ->constrained('organizational_units')->onDelete('set null')
                ->comment('Ámbito de dependencia permitido para evaluar');

            // Modificar enum de status a solo ACTIVE/INACTIVE
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE')->change();

            // Recrear foreign key user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            // Recrear índices
            $table->index(['user_id', 'job_posting_id']);
            $table->index(['job_posting_id', 'status']);

            // Constraint único: un usuario solo puede tener una asignación activa por convocatoria
            $table->unique(['user_id', 'job_posting_id', 'deleted_at'], 'unique_user_job_posting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jury_assignments', function (Blueprint $table) {
            // Eliminar foreign keys y constraints nuevos
            $table->dropForeign(['user_id']);
            $table->dropForeign(['dependency_scope_id']);
            $table->dropUnique('unique_user_job_posting');
            $table->dropIndex(['user_id', 'job_posting_id']);
            $table->dropIndex(['job_posting_id', 'status']);
        });

        Schema::table('jury_assignments', function (Blueprint $table) {
            // Renombrar de vuelta user_id a jury_member_id
            $table->renameColumn('user_id', 'jury_member_id');
        });

        Schema::table('jury_assignments', function (Blueprint $table) {
            // Eliminar dependency_scope_id
            $table->dropColumn('dependency_scope_id');

            // Restaurar columnas eliminadas
            $table->enum('member_type', ['TITULAR', 'SUPLENTE'])->default('TITULAR')->after('job_posting_id');
            $table->integer('order')->default(0)->after('role_in_jury');
            $table->string('assignment_resolution')->nullable()->after('assigned_at');
            $table->date('resolution_date')->nullable()->after('assignment_resolution');
            $table->boolean('is_active')->default(true)->after('status');
            $table->foreignUuid('replaced_by')->nullable()->constrained('jury_members')->onDelete('set null')->after('is_active');
            $table->text('replacement_reason')->nullable()->after('replaced_by');
            $table->timestamp('replacement_date')->nullable()->after('replacement_reason');
            $table->foreignUuid('replacement_approved_by')->nullable()->constrained('users')->onDelete('set null')->after('replacement_date');
            $table->text('excuse_reason')->nullable()->after('replacement_approved_by');
            $table->timestamp('excused_at')->nullable()->after('excuse_reason');
            $table->foreignUuid('excused_by')->nullable()->constrained('users')->onDelete('set null')->after('excused_at');
            $table->boolean('has_declared_conflicts')->default(false)->after('excused_by');
            $table->text('conflict_declaration')->nullable()->after('has_declared_conflicts');
            $table->timestamp('conflict_declared_at')->nullable()->after('conflict_declaration');
            $table->integer('max_evaluations')->nullable()->after('conflict_declared_at');
            $table->integer('current_evaluations')->default(0)->after('max_evaluations');
            $table->integer('completed_evaluations')->default(0)->after('current_evaluations');
            $table->date('available_from')->nullable()->after('completed_evaluations');
            $table->date('available_until')->nullable()->after('available_from');
            $table->boolean('notified')->default(false)->after('available_until');
            $table->timestamp('notified_at')->nullable()->after('notified');
            $table->boolean('accepted')->nullable()->after('notified_at');
            $table->timestamp('accepted_at')->nullable()->after('accepted');

            // Restaurar enum status original
            $table->enum('status', ['ACTIVE', 'REPLACED', 'EXCUSED', 'REMOVED', 'SUSPENDED'])->default('ACTIVE')->change();

            // Restaurar foreign key
            $table->foreign('jury_member_id')->references('id')->on('jury_members')->onDelete('restrict');

            // Restaurar índices
            $table->index(['jury_member_id', 'job_posting_id']);
            $table->unique(['jury_member_id', 'job_posting_id', 'member_type', 'deleted_at'], 'unique_jury_assignment');
        });
    }
};
