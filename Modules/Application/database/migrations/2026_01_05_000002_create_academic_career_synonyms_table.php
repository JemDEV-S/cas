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
        Schema::create('academic_career_synonyms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('career_id')->comment('FK a academic_careers');
            $table->string('synonym', 255)->comment('Variante o sinónimo del nombre de la carrera');
            $table->string('source', 50)->default('MANUAL')->comment('SUNEDU, MANUAL, LEGACY');
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('career_id')
                ->references('id')
                ->on('academic_careers')
                ->onDelete('cascade');

            // Índices
            $table->unique('synonym');
            $table->index('career_id');
            $table->index('source');
            $table->fullText('synonym');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_career_synonyms');
    }
};
