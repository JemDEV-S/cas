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
        Schema::create('application_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('applications')->onDelete('cascade');

            // Tipo de documento
            $table->string('document_type', 50)->comment('DOC_APPLICATION_FORM, DOC_CV, DOC_DNI, DOC_DEGREE, DOC_CERTIFICATE, DOC_EXPERIENCE, DOC_SPECIAL_CONDITION');

            // Información del archivo
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_extension', 10);
            $table->integer('file_size')->comment('Tamaño en bytes');
            $table->string('mime_type', 100);

            // Hash para verificación de integridad
            $table->string('file_hash', 64)->comment('SHA-256 hash');

            // Firma digital (si aplica)
            $table->boolean('requires_signature')->default(false);
            $table->boolean('is_signed')->default(false);
            $table->text('signature_data')->nullable()->comment('Datos de firma digital');
            $table->timestamp('signed_at')->nullable();

            // Validación
            $table->boolean('is_verified')->default(false);
            $table->foreignUuid('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            // Metadatos
            $table->text('description')->nullable();
            $table->foreignUuid('uploaded_by')->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('application_id');
            $table->index('document_type');
            $table->index(['application_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
