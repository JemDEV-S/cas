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
        Schema::table('evaluations', function (Blueprint $table) {
            // Agregar la columna como nullable para datos existentes
            $table->unsignedBigInteger('evaluator_assignment_id')->nullable()->after('uuid');

            // Crear la foreign key
            $table->foreign('evaluator_assignment_id')
                  ->references('id')
                  ->on('evaluator_assignments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['evaluator_assignment_id']);
            $table->dropColumn('evaluator_assignment_id');
        });
    }
};
