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
        Schema::table('job_postings', function (Blueprint $table) {
            $table->boolean('results_published')
                ->default(false)
                ->after('status')
                ->comment('Indica si los resultados de elegibilidad fueron publicados');

            $table->timestamp('results_published_at')
                ->nullable()
                ->after('results_published')
                ->comment('Fecha de publicación de resultados');

            $table->foreignUuid('results_published_by')
                ->nullable()
                ->after('results_published_at')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que publicó los resultados');

            // Índice
            $table->index('results_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropForeign(['results_published_by']);
            $table->dropIndex(['results_published']);
            $table->dropColumn([
                'results_published',
                'results_published_at',
                'results_published_by'
            ]);
        });
    }
};
