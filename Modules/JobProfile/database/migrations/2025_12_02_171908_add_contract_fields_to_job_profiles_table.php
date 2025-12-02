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
        Schema::table('job_profiles', function (Blueprint $table) {
            // Campos de contrato
            $table->date('contract_start_date')->nullable()->after('justification');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->string('work_location')->nullable()->after('contract_end_date');

            // Campo para almacenar el proceso de selección (ej: "PROCESO DE SELECCIÓN CAS VI-2025")
            $table->string('selection_process_name')->nullable()->after('work_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'contract_start_date',
                'contract_end_date',
                'work_location',
                'selection_process_name',
            ]);
        });
    }
};
