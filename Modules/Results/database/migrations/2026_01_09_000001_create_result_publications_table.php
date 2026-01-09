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
        Schema::create('result_publications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relaciones
            $table->foreignUuid('job_posting_id')
                ->constrained('job_postings')
                ->cascadeOnDelete()
                ->comment('Convocatoria a la que pertenecen los resultados');

            $table->foreignUuid('generated_document_id')
                ->nullable()
                ->constrained('generated_documents')
                ->nullOnDelete()
                ->comment('Documento oficial con firmas digitales');

            // Datos principales
            $table->enum('phase', ['PHASE_04', 'PHASE_07', 'PHASE_09'])
                ->comment('Fase de la convocatoria');

            $table->enum('status', ['draft', 'pending_signature', 'published', 'unpublished', 'cancelled'])
                ->default('draft')
                ->comment('Estado de la publicación');

            $table->string('title')->comment('Título de la publicación');
            $table->text('description')->nullable()->comment('Descripción opcional');

            // Exportación Excel
            $table->string('excel_path')->nullable()->comment('Ruta del archivo Excel');

            // Estadísticas
            $table->integer('total_applicants')->default(0)->comment('Total de postulantes');
            $table->integer('total_eligible')->nullable()->comment('Total de APTOS (solo Fase 4)');
            $table->integer('total_not_eligible')->nullable()->comment('Total de NO APTOS (solo Fase 4)');

            // Control de publicación
            $table->timestamp('published_at')->nullable()->comment('Fecha de publicación');
            $table->foreignUuid('published_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que publicó');

            $table->timestamp('unpublished_at')->nullable()->comment('Fecha de despublicación');
            $table->foreignUuid('unpublished_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que despublicó');

            // Metadata JSON (flexible para datos adicionales)
            $table->json('metadata')->nullable()->comment('Datos adicionales en formato JSON');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('job_posting_id');
            $table->index('phase');
            $table->index('status');
            $table->index(['job_posting_id', 'phase']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_publications');
    }
};
