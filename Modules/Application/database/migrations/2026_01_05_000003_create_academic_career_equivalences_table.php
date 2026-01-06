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
        Schema::create('academic_career_equivalences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('career_id')->comment('Carrera A');
            $table->uuid('equivalent_career_id')->comment('Carrera B equivalente');
            $table->string('equivalence_type', 50)->default('MANUAL')->comment('MANUAL, CATEGORY_GROUP');
            $table->text('notes')->nullable()->comment('Justificación de la equivalencia');
            $table->uuid('approved_by')->nullable()->comment('Usuario que aprobó');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('career_id')
                ->references('id')
                ->on('academic_careers')
                ->onDelete('cascade');

            $table->foreign('equivalent_career_id')
                ->references('id')
                ->on('academic_careers')
                ->onDelete('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Índices y constraints
            $table->unique(['career_id', 'equivalent_career_id'], 'ac_career_equiv_unique');
            $table->index('career_id');
            $table->index('equivalent_career_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_career_equivalences');
    }
};
