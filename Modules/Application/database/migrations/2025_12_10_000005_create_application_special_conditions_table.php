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
        Schema::create('application_special_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('applications')->onDelete('cascade');

            // Tipo de condición especial
            $table->string('condition_type', 50)->comment('DISABILITY, MILITARY, ATHLETE_NATIONAL, ATHLETE_INTL, TERRORISM');

            // Datos del registro/documento
            $table->string('issuing_entity')->nullable();
            $table->string('document_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Bonificación
            $table->decimal('bonus_percentage', 5, 2)->comment('Porcentaje de bonificación (10, 15, etc)');

            // Validación
            $table->boolean('is_verified')->default(false);
            $table->text('verification_notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index('application_id');
            $table->index('condition_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_special_conditions');
    }
};
