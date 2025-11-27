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
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('generated_document_id');
            $table->uuid('user_id');

            // Datos de la firma
            $table->string('signature_type', 30)->default('firma')->comment('firma, visto_bueno, aprobacion, conformidad');
            $table->integer('signature_order')->default(1)->comment('Orden de firma en el flujo');
            $table->string('role')->nullable()->comment('Rol del firmante');

            // Datos del certificado digital
            $table->json('certificate_data')->nullable()->comment('Datos completos del certificado');
            $table->string('certificate_issuer')->nullable()->comment('Emisor del certificado');
            $table->string('certificate_serial')->nullable()->comment('Número de serie');
            $table->timestamp('certificate_valid_from')->nullable();
            $table->timestamp('certificate_valid_to')->nullable();

            // Archivo firmado
            $table->string('signed_document_path')->nullable()->comment('Ruta del documento firmado');
            $table->timestamp('signature_timestamp')->nullable()->comment('Timestamp de la firma digital');

            // Datos de auditoría
            $table->timestamp('signed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Estado
            $table->string('status', 20)->default('pending')->comment('pending, signed, rejected, cancelled');
            $table->text('rejection_reason')->nullable();

            // Metadata adicional
            $table->json('signature_metadata')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('generated_document_id')->references('id')->on('generated_documents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            // Índices
            $table->index('generated_document_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('signature_order');
            $table->index('signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
