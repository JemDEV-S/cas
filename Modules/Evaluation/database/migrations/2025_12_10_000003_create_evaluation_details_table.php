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
        Schema::create('evaluation_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Relaciones
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->foreignId('criterion_id')->constrained('evaluation_criteria')->onDelete('restrict');
            
            // Calificación
            $table->decimal('score', 8, 2)->comment('Puntaje otorgado');
            $table->decimal('weighted_score', 8, 2)->nullable()->comment('Puntaje ponderado');
            
            // Comentarios y evidencia
            $table->text('comments')->nullable()->comment('Comentarios del evaluador');
            $table->text('evidence')->nullable()->comment('Evidencia o justificación');
            
            // Control de versiones
            $table->integer('version')->default(1);
            $table->text('change_reason')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->unique(['evaluation_id', 'criterion_id']);
            $table->index('criterion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_details');
    }
};