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
        Schema::table('position_codes', function (Blueprint $table) {
            // Experiencia profesional mínima requerida (en años)
            $table->decimal('min_professional_experience', 3, 1)->nullable()->after('description');
            
            // Experiencia específica mínima requerida (en años)
            $table->decimal('min_specific_experience', 3, 1)->nullable()->after('min_professional_experience');
            
            // ¿Requiere título profesional?
            $table->boolean('requires_professional_title')->default(false)->after('min_specific_experience');
            
            // ¿Requiere habilitación profesional (colegiatura)?
            $table->boolean('requires_professional_license')->default(false)->after('requires_professional_title');
            
            // Nivel educativo requerido (puede ser: 'secondary', 'technical', 'bachelor', 'professional', etc.)
            $table->string('education_level_required')->nullable()->after('requires_professional_license');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position_codes', function (Blueprint $table) {
            $table->dropColumn([
                'min_professional_experience',
                'min_specific_experience',
                'requires_professional_title',
                'requires_professional_license',
                'education_level_required',
            ]);
        });
    }
};