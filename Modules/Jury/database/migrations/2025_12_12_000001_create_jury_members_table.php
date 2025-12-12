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
        Schema::create('jury_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relación con usuario
            $table->foreignUuid('user_id')->unique()->constrained('users')->onDelete('restrict')
                ->comment('Usuario que es jurado');
            
            // Información profesional
            $table->string('specialty')->nullable()->comment('Especialidad o área de expertise');
            $table->integer('years_of_experience')->nullable()->comment('Años de experiencia profesional');
            $table->string('professional_title')->nullable()->comment('Título profesional principal');
            $table->text('bio')->nullable()->comment('Biografía o descripción profesional');
            
            // Estado y disponibilidad
            $table->boolean('is_active')->default(true)->comment('Jurado activo en el sistema');
            $table->boolean('is_available')->default(true)->comment('Disponible para asignaciones');
            $table->text('unavailability_reason')->nullable()->comment('Razón de no disponibilidad');
            $table->date('unavailable_from')->nullable();
            $table->date('unavailable_until')->nullable();
            
            // Capacitación
            $table->boolean('training_completed')->default(false)->comment('Completó capacitación de jurado');
            $table->timestamp('training_completed_at')->nullable();
            $table->string('training_certificate_path')->nullable()->comment('Ruta del certificado');
            
            // Estadísticas de desempeño
            $table->integer('total_evaluations')->default(0)->comment('Total de evaluaciones realizadas');
            $table->integer('total_assignments')->default(0)->comment('Total de asignaciones recibidas');
            $table->integer('average_evaluation_time')->nullable()->comment('Tiempo promedio en minutos');
            $table->decimal('consistency_score', 5, 2)->nullable()->comment('Puntuación de consistencia 0-100');
            $table->decimal('average_rating', 3, 2)->nullable()->comment('Calificación promedio del jurado');
            
            // Preferencias
            $table->json('preferred_areas')->nullable()->comment('Áreas de preferencia para evaluar');
            $table->integer('max_concurrent_assignments')->default(20)->comment('Máximo de asignaciones simultáneas');
            
            // Metadata y auditoría
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('user_id');
            $table->index(['is_active', 'is_available']);
            $table->index('specialty');
            $table->index('training_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jury_members');
    }
};