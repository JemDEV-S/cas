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
        Schema::create('job_posting_history', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relación
            $table->uuid('job_posting_id')->comment('Convocatoria afectada');

            // Usuario que realizó la acción
            $table->uuid('user_id')->nullable()->comment('Usuario que realizó la acción');

            // Acción realizada
            $table->string('action', 50)->comment('Acción realizada (created, updated, published, cancelled, etc.)');

            // Cambios de estado
            $table->string('previous_status', 30)->nullable()->comment('Estado anterior');
            $table->string('new_status', 30)->nullable()->comment('Estado nuevo');

            // Descripción de los cambios
            $table->text('description')->nullable()->comment('Descripción detallada de la acción');

            // Cambios realizados (diff)
            $table->jsonb('changes')->nullable()->comment('Detalles de los cambios realizados');

            // IP y User Agent
            $table->string('ip_address', 45)->nullable()->comment('Dirección IP del usuario');
            $table->text('user_agent')->nullable()->comment('User agent del navegador');

            // Metadata
            $table->jsonb('metadata')->nullable()->comment('Información adicional');

            // Timestamp
            $table->timestamp('created_at')->useCurrent()->comment('Fecha y hora de la acción');

            // Índices
            $table->index('job_posting_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['job_posting_id', 'action']);
            $table->index(['job_posting_id', 'created_at']);
            $table->index(['user_id', 'created_at']);

            // Foreign Keys
            $table->foreign('job_posting_id')
                ->references('id')
                ->on('job_postings')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posting_history');
    }
};
