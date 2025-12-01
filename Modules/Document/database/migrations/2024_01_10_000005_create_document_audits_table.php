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
        Schema::create('document_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('generated_document_id');
            $table->uuid('user_id')->nullable();

            // Acción realizada
            $table->string('action', 50)->comment('created, updated, viewed, downloaded, signed, rejected, deleted, restored');
            $table->text('description');

            // Valores antes y después (para updates)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Datos de auditoría
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('generated_document_id')->references('id')->on('generated_documents')->onDelete('cascade');

            // Índices
            $table->index('generated_document_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_audits');
    }
};
