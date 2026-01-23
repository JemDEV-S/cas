<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Modifica evaluator_assignments según diseño optimizado:
     * - Cambia evaluator_id a user_id (relación directa con users)
     * - Elimina campos de workload_weight, has_conflict, is_available
     * - Simplifica a campos esenciales
     */
    public function up(): void
    {
        Schema::table('evaluator_assignments', function (Blueprint $table) {
            // Eliminar foreign key y constraint antiguo
            $table->dropForeign(['evaluator_id']);
            $table->dropUnique('unique_evaluator_application_phase');
            $table->dropIndex(['evaluator_id', 'status']);
        });

        Schema::table('evaluator_assignments', function (Blueprint $table) {
            // Renombrar evaluator_id a user_id
            $table->renameColumn('evaluator_id', 'user_id');
        });

        Schema::table('evaluator_assignments', function (Blueprint $table) {
            // Eliminar columnas innecesarias
            $table->dropColumn([
                'workload_weight',
                'has_conflict',
                'conflict_reason',
                'is_available',
                'unavailability_reason',
                'notified',
                'notified_at',
            ]);

            // Recrear foreign key user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Recrear índices
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'job_posting_id']);

            // Constraint único actualizado: una asignación única por usuario-aplicación-fase
            $table->unique(['user_id', 'application_id', 'phase_id'], 'unique_user_application_phase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluator_assignments', function (Blueprint $table) {
            // Eliminar foreign keys y constraints nuevos
            $table->dropForeign(['user_id']);
            $table->dropUnique('unique_user_application_phase');
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'job_posting_id']);
        });

        Schema::table('evaluator_assignments', function (Blueprint $table) {
            // Renombrar de vuelta user_id a evaluator_id
            $table->renameColumn('user_id', 'evaluator_id');
        });

        Schema::table('evaluator_assignments', function (Blueprint $table) {
            // Restaurar columnas eliminadas
            $table->integer('workload_weight')->default(1)->after('status');
            $table->boolean('has_conflict')->default(false)->after('completed_at');
            $table->text('conflict_reason')->nullable()->after('has_conflict');
            $table->boolean('is_available')->default(true)->after('conflict_reason');
            $table->text('unavailability_reason')->nullable()->after('is_available');
            $table->boolean('notified')->default(false)->after('unavailability_reason');
            $table->timestamp('notified_at')->nullable()->after('notified');

            // Restaurar foreign key
            $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('cascade');

            // Restaurar índices
            $table->index(['evaluator_id', 'status']);
            $table->unique(['evaluator_id', 'application_id', 'phase_id'], 'unique_evaluator_application_phase');
        });
    }
};
