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
        Schema::create('application_trainings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('applications')->onDelete('cascade');

            // Datos de capacitación/cursos
            $table->string('institution');
            $table->string('course_name');
            $table->integer('academic_hours')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Validación
            $table->boolean('is_verified')->default(false);
            $table->text('verification_notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index('application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_trainings');
    }
};
