<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Bonificación por Licenciado FF.AA. (RPE 61-2010-SERVIR/PE Art. 4)
            $table->decimal('military_bonus', 5, 2)->nullable()
                  ->after('age_bonus')
                  ->comment('Bonificacion 10% sobre entrevista RAW para licenciados FF.AA.');
        });

        // Actualizar el comentario del campo interview_score_with_bonus
        DB::statement("ALTER TABLE applications MODIFY COLUMN interview_score_with_bonus DECIMAL(5,2) NULL COMMENT 'interview_score + age_bonus + military_bonus'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('military_bonus');
        });

        // Restaurar comentario original
        DB::statement("ALTER TABLE applications MODIFY COLUMN interview_score_with_bonus DECIMAL(5,2) NULL COMMENT 'interview_score + age_bonus'");
    }
};
