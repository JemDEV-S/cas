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
            $table->boolean('is_related_career')->default(false)->after('career_id')
                ->comment('Indica si el postulante declaró una carrera afín no catalogada');
            $table->string('related_career_name', 200)->nullable()->after('is_related_career')
                ->comment('Nombre de la carrera afín si is_related_career = true');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_academics', function (Blueprint $table) {
            $table->dropColumn(['is_related_career', 'related_career_name']);
        });
    }
};
