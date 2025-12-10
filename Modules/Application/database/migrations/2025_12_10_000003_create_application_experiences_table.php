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
        Schema::create('application_experiences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('applications')->onDelete('cascade');

            // Datos de la experiencia laboral
            $table->string('organization');
            $table->string('position');
            $table->date('start_date');
            $table->date('end_date');

            // Clasificación
            $table->boolean('is_specific')->default(false)->comment('Es experiencia específica en el área del cargo');
            $table->boolean('is_public_sector')->default(false)->comment('Es sector público');

            // Cálculo de días (computed)
            $table->integer('duration_days')->nullable()->comment('Días calculados');

            // Validación
            $table->boolean('is_verified')->default(false);
            $table->text('verification_notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index('application_id');
            $table->index(['application_id', 'is_specific']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_experiences');
    }
};
