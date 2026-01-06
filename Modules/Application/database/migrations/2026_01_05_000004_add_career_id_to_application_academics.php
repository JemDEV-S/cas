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
        Schema::table('application_academics', function (Blueprint $table) {
            // Agregar career_id después de career_field
            $table->uuid('career_id')->nullable()->after('career_field')->comment('FK a academic_careers');

            // Foreign key
            $table->foreign('career_id')
                ->references('id')
                ->on('academic_careers')
                ->onDelete('set null');

            // Índice
            $table->index('career_id');
        });

        // Nota: career_field se mantiene para compatibilidad histórica
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_academics', function (Blueprint $table) {
            $table->dropForeign(['career_id']);
            $table->dropIndex(['career_id']);
            $table->dropColumn('career_id');
        });
    }
};
