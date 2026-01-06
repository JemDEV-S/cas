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
        Schema::create('temp_job_profile_career_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_profile_id')->comment('FK a job_profiles');
            $table->uuid('career_id')->nullable()->comment('FK a academic_careers');
            $table->string('original_text', 255)->nullable()->comment('Texto original del career_field');
            $table->decimal('confidence_score', 5, 2)->nullable()->comment('Score de confianza 0-100');
            $table->string('status', 20)->default('PENDING_REVIEW')->comment('PENDING_REVIEW, APPROVED, REJECTED');
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable()->comment('Notas del revisor');
            $table->timestamps();

            // Foreign keys
            $table->foreign('job_profile_id')
                ->references('id')
                ->on('job_profiles')
                ->onDelete('cascade');

            $table->foreign('career_id')
                ->references('id')
                ->on('academic_careers')
                ->onDelete('set null');

            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Índices
            $table->index('status');
            $table->index('job_profile_id');
            $table->index('career_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_job_profile_career_mappings');
    }
};
