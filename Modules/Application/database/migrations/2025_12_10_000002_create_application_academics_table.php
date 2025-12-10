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
        Schema::create('application_academics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('applications')->onDelete('cascade');

            // Datos de formación académica
            $table->string('institution_name');
            $table->string('degree_type', 50)->comment('SECUNDARIA, TECNICO, BACHILLER, TITULO, MAESTRIA, DOCTORADO');
            $table->string('career_field')->nullable()->comment('Carrera o especialidad');
            $table->string('degree_title');
            $table->date('issue_date');

            // Validación
            $table->boolean('is_verified')->default(false);
            $table->text('verification_notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index('application_id');
            $table->index('degree_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_academics');
    }
};
