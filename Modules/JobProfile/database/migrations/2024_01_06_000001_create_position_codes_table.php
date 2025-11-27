<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique()->comment('Código único del cargo, ej: CAP-001');
            $table->string('name')->comment('Nombre del cargo');
            $table->text('description')->nullable();

            // Salarios y cálculos
            $table->decimal('base_salary', 10, 2)->comment('Salario base mensual');
            $table->decimal('essalud_percentage', 5, 2)->default(9.0)->comment('Porcentaje de EsSalud');
            $table->decimal('essalud_amount', 10, 2)->storedAs('base_salary * (essalud_percentage / 100)')->comment('Monto calculado de EsSalud');
            $table->decimal('monthly_total', 10, 2)->storedAs('base_salary + (base_salary * (essalud_percentage / 100))')->comment('Total mensual con EsSalud');
            $table->integer('contract_months')->default(3)->comment('Duración del contrato en meses');
            $table->decimal('quarterly_total', 10, 2)->storedAs('(base_salary + (base_salary * (essalud_percentage / 100))) * contract_months')->comment('Total por periodo de contrato');

            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_codes');
    }
};
