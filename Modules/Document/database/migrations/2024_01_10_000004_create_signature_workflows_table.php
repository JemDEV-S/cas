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
        Schema::create('signature_workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('generated_document_id');

            // Configuración del flujo
            $table->string('workflow_type', 20)->default('sequential')->comment('sequential o parallel');
            $table->integer('current_step')->default(1);
            $table->integer('total_steps');
            $table->json('signers_order')->comment('Orden de firmantes con configuración');

            // Estado
            $table->string('status', 20)->default('pending')->comment('pending, in_progress, completed, cancelled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('generated_document_id')->references('id')->on('generated_documents')->onDelete('cascade');

            // Índices
            $table->index('generated_document_id');
            $table->index('status');
            $table->index('workflow_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_workflows');
    }
};
