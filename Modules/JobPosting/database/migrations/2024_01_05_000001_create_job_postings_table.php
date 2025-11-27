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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identificación
            $table->string('code', 50)->unique()->comment('Código único auto-generado (CONV-2025-001)');
            $table->string('title')->comment('Título de la convocatoria');
            $table->integer('year')->comment('Año de la convocatoria');
            $table->text('description')->nullable()->comment('Descripción de la convocatoria');

            // Estado
            $table->string('status', 30)->default('BORRADOR')->comment('Estado: BORRADOR, PUBLICADA, EN_PROCESO, FINALIZADA, CANCELADA');

            // Fechas tentativas
            $table->date('start_date')->nullable()->comment('Fecha de inicio tentativa');
            $table->date('end_date')->nullable()->comment('Fecha de fin tentativa');

            // Publicación
            $table->timestamp('published_at')->nullable()->comment('Fecha y hora de publicación');
            $table->uuid('published_by')->nullable()->comment('Usuario que publicó');

            // Finalización
            $table->timestamp('finalized_at')->nullable()->comment('Fecha y hora de finalización');
            $table->uuid('finalized_by')->nullable()->comment('Usuario que finalizó');

            // Cancelación
            $table->timestamp('cancelled_at')->nullable()->comment('Fecha y hora de cancelación');
            $table->uuid('cancelled_by')->nullable()->comment('Usuario que canceló');
            $table->text('cancellation_reason')->nullable()->comment('Razón de cancelación');

            // Metadata
            $table->jsonb('metadata')->nullable()->comment('Metadatos adicionales');

            // Timestamps y Soft Deletes
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('code');
            $table->index('year');
            $table->index('status');
            $table->index(['year', 'status']);
            $table->index('published_at');
            $table->index('start_date');
            $table->index('end_date');

            // Foreign Keys
            $table->foreign('published_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('finalized_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('cancelled_by')
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
        Schema::dropIfExists('job_postings');
    }
};
