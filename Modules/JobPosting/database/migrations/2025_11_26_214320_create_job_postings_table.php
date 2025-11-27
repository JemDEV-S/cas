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
            // Primary Key
            $table->uuid('id')->primary();
            
            // Identificación
            $table->string('code', 50)->unique()->comment('Código único auto-generado: CONV-YYYY-NNN');
            $table->string('title')->comment('Título de la convocatoria');
            $table->year('year')->comment('Año de la convocatoria');
            
            // Descripción
            $table->text('description')->nullable()->comment('Descripción general de la convocatoria');
            
            // Estado (BORRADOR, PUBLICADA, EN_PROCESO, FINALIZADA, CANCELADA)
            $table->string('status', 50)->default('BORRADOR')->comment('Estado actual de la convocatoria');
            
            // Fechas de convocatoria
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
            $table->text('cancellation_reason')->nullable()->comment('Motivo de cancelación');
            
            // Metadata
            $table->json('metadata')->nullable()->comment('Información adicional en formato JSON');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('finalized_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('code');
            $table->index('year');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['year', 'status']);
            $table->index('created_at');
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