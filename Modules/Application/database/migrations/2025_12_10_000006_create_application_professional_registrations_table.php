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
        Schema::create('application_professional_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('applications')->onDelete('cascade');

            // Tipo de registro
            $table->string('registration_type', 50)->comment('COLEGIATURA, OSCE_CERTIFICATION, DRIVER_LICENSE');

            // Datos del registro
            $table->string('issuing_entity')->nullable()->comment('Colegio profesional, OSCE, MTC');
            $table->string('registration_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Validación
            $table->boolean('is_verified')->default(false);
            $table->text('verification_notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index('application_id');
            $table->index('registration_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_professional_registrations');
    }
};
