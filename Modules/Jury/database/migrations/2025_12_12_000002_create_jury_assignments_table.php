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
        Schema::create('jury_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relaciones principales
            $table->foreignUuid('jury_member_id')->constrained('jury_members')->onDelete('restrict')
                ->comment('Jurado asignado');
            $table->foreignUuid('job_posting_id')->constrained('job_postings')->onDelete('cascade')
                ->comment('Convocatoria');
            
            // Tipo y rol del jurado
            $table->enum('member_type', ['TITULAR', 'SUPLENTE'])->default('TITULAR')
                ->comment('Tipo de miembro del jurado');
            $table->enum('role_in_jury', ['PRESIDENTE', 'SECRETARIO', 'VOCAL', 'MIEMBRO'])->nullable()
                ->comment('Rol específico en el jurado');
            $table->integer('order')->default(0)->comment('Orden en el acta/listado');
            
            // Información de asignación
            $table->foreignUuid('assigned_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Usuario que realizó la asignación');
            $table->timestamp('assigned_at')->nullable()->comment('Fecha de asignación');
            $table->string('assignment_resolution')->nullable()->comment('Número de resolución/acta');
            $table->date('resolution_date')->nullable()->comment('Fecha de la resolución');
            
            // Estado
            $table->enum('status', ['ACTIVE', 'REPLACED', 'EXCUSED', 'REMOVED', 'SUSPENDED'])->default('ACTIVE');
            $table->boolean('is_active')->default(true);
            
            // Reemplazo
            $table->foreignUuid('replaced_by')->nullable()->constrained('jury_members')->onDelete('set null')
                ->comment('Jurado que lo reemplazó');
            $table->text('replacement_reason')->nullable();
            $table->timestamp('replacement_date')->nullable();
            $table->foreignUuid('replacement_approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Excusa
            $table->text('excuse_reason')->nullable();
            $table->timestamp('excused_at')->nullable();
            $table->foreignUuid('excused_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Conflictos de interés
            $table->boolean('has_declared_conflicts')->default(false)
                ->comment('Ha declarado conflictos de interés');
            $table->text('conflict_declaration')->nullable();
            $table->timestamp('conflict_declared_at')->nullable();
            
            // Carga de trabajo
            $table->integer('max_evaluations')->nullable()->comment('Límite de evaluaciones para esta asignación');
            $table->integer('current_evaluations')->default(0)->comment('Evaluaciones actuales asignadas');
            $table->integer('completed_evaluations')->default(0)->comment('Evaluaciones completadas');
            
            // Disponibilidad temporal
            $table->date('available_from')->nullable()->comment('Disponible desde');
            $table->date('available_until')->nullable()->comment('Disponible hasta');
            
            // Notificaciones
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->boolean('accepted')->nullable()->comment('Aceptó la asignación');
            $table->timestamp('accepted_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['jury_member_id', 'job_posting_id']);
            $table->index('job_posting_id');
            $table->index(['status', 'is_active']);
            $table->index('member_type');
            $table->index('role_in_jury');
            $table->index(['job_posting_id', 'member_type', 'status']);
            
            // Constraint: Un jurado no puede ser titular y suplente a la vez en la misma convocatoria
            $table->unique(['jury_member_id', 'job_posting_id', 'member_type', 'deleted_at'], 'unique_jury_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jury_assignments');
    }
};