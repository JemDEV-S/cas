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
        Schema::table('evaluator_assignments', function (Blueprint $table) {
            // Campos para programación de entrevistas
            $table->dateTime('interview_scheduled_at')->nullable()->after('deadline_at')
                ->comment('Fecha y hora programada de la entrevista');
            $table->integer('interview_duration_minutes')->nullable()->after('interview_scheduled_at')
                ->default(30)->comment('Duración de la entrevista en minutos');
            $table->string('interview_location')->nullable()->after('interview_duration_minutes')
                ->comment('Ubicación de la entrevista (presencial o virtual)');
            $table->text('interview_notes')->nullable()->after('interview_location')
                ->comment('Notas adicionales sobre la entrevista');

            // Índice para búsquedas por fecha de entrevista
            $table->index('interview_scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluator_assignments', function (Blueprint $table) {
            $table->dropIndex(['interview_scheduled_at']);
            $table->dropColumn([
                'interview_scheduled_at',
                'interview_duration_minutes',
                'interview_location',
                'interview_notes',
            ]);
        });
    }
};
