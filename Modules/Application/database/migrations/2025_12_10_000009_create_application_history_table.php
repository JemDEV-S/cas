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
        Schema::create('application_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('applications')->onDelete('cascade');

            // Tipo de evento
            $table->string('event_type', 50)->comment('CREATED, UPDATED, STATUS_CHANGED, DOCUMENT_UPLOADED, EVALUATED, COMMENTED, etc.');

            // Estado anterior y nuevo (para cambios de estado)
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();

            // Datos del cambio
            $table->json('old_values')->nullable()->comment('Valores anteriores');
            $table->json('new_values')->nullable()->comment('Valores nuevos');

            // Descripción del evento
            $table->text('description')->nullable();
            $table->text('comments')->nullable();

            // Usuario que realizó la acción
            $table->foreignUuid('performed_by')->constrained('users');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // Metadatos adicionales
            $table->json('metadata')->nullable();

            $table->timestamp('performed_at')->useCurrent();

            // Índices
            $table->index('application_id');
            $table->index('event_type');
            $table->index('performed_by');
            $table->index('performed_at');
            $table->index(['application_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_history');
    }
};
