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
        Schema::create('evaluation_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Relaciones - ACTUALIZADO PARA UUID
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('restrict')
                ->comment('Usuario que realizó el cambio');
            
            // Tipo de cambio
            $table->enum('action_type', [
                'CREATED',          // Creada
                'UPDATED',          // Actualizada
                'SUBMITTED',        // Enviada
                'MODIFIED',         // Modificada (después de envío)
                'SCORE_CHANGED',    // Cambio de puntaje
                'CRITERION_CHANGED', // Cambio en criterio específico
                'STATUS_CHANGED',   // Cambio de estado
                'REASSIGNED',       // Reasignada
                'CANCELLED'         // Cancelada
            ]);
            
            // Datos del cambio
            $table->text('description')->nullable()->comment('Descripción del cambio');
            $table->text('reason')->nullable()->comment('Razón del cambio');
            
            // Versión anterior (snapshot)
            $table->json('old_values')->nullable()->comment('Valores anteriores');
            $table->json('new_values')->nullable()->comment('Nuevos valores');
            
            // Contexto
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['evaluation_id', 'created_at']);
            $table->index('user_id');
            $table->index('action_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_history');
    }
};