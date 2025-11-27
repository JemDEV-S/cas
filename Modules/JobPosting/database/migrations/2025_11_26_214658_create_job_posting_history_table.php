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
            // Primary Key
            $table->uuid('id')->primary();
            
            // Relación
            $table->uuid('job_posting_id')->comment('ID de la convocatoria');
            
            // Usuario y acción
            $table->uuid('user_id')->nullable()->comment('Usuario que realizó el cambio');
            $table->string('action', 100)->comment('Tipo de acción: created, updated, published, finalized, cancelled, etc.');
            
            // Cambios de estado
            $table->string('old_status', 50)->nullable()->comment('Estado anterior');
            $table->string('new_status', 50)->nullable()->comment('Estado nuevo');
            
            // Datos del cambio
            $table->json('old_values')->nullable()->comment('Valores anteriores (JSON)');
            $table->json('new_values')->nullable()->comment('Valores nuevos (JSON)');
            $table->text('description')->nullable()->comment('Descripción del cambio');
            $table->text('reason')->nullable()->comment('Razón del cambio (para cancelaciones, etc.)');
            
            // Metadata técnico
            $table->string('ip_address', 45)->nullable()->comment('IP del usuario');
            $table->string('user_agent')->nullable()->comment('Navegador/dispositivo');
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign Keys
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('job_posting_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['job_posting_id', 'created_at']);
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