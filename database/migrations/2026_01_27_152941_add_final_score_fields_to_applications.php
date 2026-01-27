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
        Schema::table('applications', function (Blueprint $table) {
            // Bonificacion por edad (Ley 31533)
            $table->decimal('age_bonus', 5, 2)->nullable()
                  ->after('interview_score')
                  ->comment('Bonificacion 10% para menores de 29 anios');

            // Puntaje entrevista con bonus joven
            $table->decimal('interview_score_with_bonus', 5, 2)->nullable()
                  ->after('age_bonus')
                  ->comment('interview_score + age_bonus');

            // Puntaje base (CV + Entrevista con bonus)
            $table->decimal('base_score', 5, 2)->nullable()
                  ->after('interview_score_with_bonus')
                  ->comment('curriculum_score + interview_score_with_bonus');

            // Experiencia sector publico
            $table->integer('public_sector_years')->nullable()
                  ->after('base_score')
                  ->comment('Anios de experiencia en sector publico');

            $table->decimal('public_sector_bonus', 5, 2)->nullable()
                  ->after('public_sector_years')
                  ->comment('Bonificacion por exp. sector publico (max 3 pts)');

            // Total bonificaciones especiales
            $table->decimal('special_bonus_total', 5, 2)->nullable()
                  ->after('public_sector_bonus')
                  ->comment('Suma de bonificaciones por discapacidad, militar, etc');

            // Resultado de seleccion
            $table->enum('selection_result', ['GANADOR', 'ACCESITARIO', 'NO_SELECCIONADO', 'NO_APTO'])
                  ->nullable()
                  ->after('final_ranking')
                  ->comment('Resultado del proceso de seleccion');

            // Orden de accesitario
            $table->integer('accesitario_order')->nullable()
                  ->after('selection_result')
                  ->comment('Orden de prioridad si es accesitario (1, 2, 3...)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'age_bonus',
                'interview_score_with_bonus',
                'base_score',
                'public_sector_years',
                'public_sector_bonus',
                'special_bonus_total',
                'selection_result',
                'accesitario_order',
            ]);
        });
    }
};
