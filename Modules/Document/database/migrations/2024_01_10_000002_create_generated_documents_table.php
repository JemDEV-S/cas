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
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique()->comment('Código único del documento');
            $table->uuid('document_template_id');

            // Relación polimórfica
            $table->uuid('documentable_id')->comment('ID de la entidad relacionada');
            $table->string('documentable_type')->comment('Tipo de entidad: JobProfile, JobPosting, etc.');

            // Contenido
            $table->string('title');
            $table->longText('content')->nullable()->comment('Contenido original renderizado');
            $table->longText('rendered_html')->nullable()->comment('HTML final renderizado');
            $table->string('pdf_path')->nullable()->comment('Ruta del PDF generado');
            $table->string('signed_pdf_path')->nullable()->comment('Ruta del PDF firmado');

            // Estado
            $table->string('status', 30)->default('draft')->comment('draft, pending_signature, signed, rejected, cancelled');
            $table->uuid('generated_by');
            $table->timestamp('generated_at')->nullable();

            // Firmas
            $table->boolean('signature_required')->default(false);
            $table->string('signature_status', 30)->nullable()->comment('pending, in_progress, completed, rejected');
            $table->integer('signatures_completed')->default(0);
            $table->integer('total_signatures_required')->default(0);
            $table->uuid('current_signer_id')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('document_template_id')->references('id')->on('document_templates')->onDelete('restrict');

            // Índices
            $table->index('code');
            $table->index(['documentable_type', 'documentable_id']);
            $table->index('status');
            $table->index('signature_status');
            $table->index('generated_by');
            $table->index('current_signer_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
    }
};
