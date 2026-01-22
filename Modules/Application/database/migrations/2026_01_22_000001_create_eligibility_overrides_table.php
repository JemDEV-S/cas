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
        Schema::create('eligibility_overrides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')
                ->unique()
                ->constrained('applications')
                ->onDelete('cascade');

            // Estado original antes del override
            $table->string('original_status', 30)
                ->comment('Estado original: NOT_ELIGIBLE, SUBMITTED, IN_REVIEW');
            $table->text('original_reason')->nullable()
                ->comment('Razón original de NO_APTO');

            // Nuevo estado después del override
            $table->string('new_status', 30)
                ->comment('Nuevo estado: ELIGIBLE o NOT_ELIGIBLE');
            $table->string('decision', 20)
                ->comment('Decisión: APPROVED o REJECTED');

            // Detalles de la resolución
            $table->string('resolution_type', 30)->default('CLAIM')
                ->comment('Tipo: CLAIM, CORRECTION, OTHER');
            $table->string('resolution_summary', 255)
                ->comment('Resumen corto de la resolución');
            $table->text('resolution_detail')
                ->comment('Detalle completo de la resolución');

            // Quién y cuándo resolvió
            $table->foreignUuid('resolved_by')
                ->constrained('users')
                ->onDelete('restrict');
            $table->timestamp('resolved_at');

            // Metadata adicional
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Índices
            $table->index('decision');
            $table->index('resolution_type');
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eligibility_overrides');
    }
};
