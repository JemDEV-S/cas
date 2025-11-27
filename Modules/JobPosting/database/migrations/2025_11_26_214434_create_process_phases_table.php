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
        Schema::create('process_phases', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();
            
            // Identificación
            $table->string('code', 50)->unique()->comment('Código único de la fase (ej: PHASE_001)');
            $table->string('name')->comment('Nombre de la fase');
            $table->text('description')->nullable()->comment('Descripción de la fase');
            
            // Configuración
            $table->integer('phase_number')->comment('Número de fase en el proceso');
            $table->integer('order')->default(0)->comment('Orden de ejecución');
            
            // Características
            $table->boolean('requires_evaluation')->default(false)->comment('Indica si requiere evaluación de jurados');
            $table->boolean('is_public')->default(true)->comment('Indica si es visible públicamente');
            $table->boolean('is_active')->default(true)->comment('Indica si la fase está activa');
            
            // Configuración de tiempos
            $table->integer('default_duration_days')->nullable()->comment('Duración predeterminada en días');
            
            // Metadata
            $table->json('metadata')->nullable()->comment('Información adicional');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('phase_number');
            $table->index('order');
            $table->index('is_active');
            $table->index(['is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_phases');
    }
};