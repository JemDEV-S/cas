<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_profile_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_profile_id');
            $table->string('category', 100); // education, experience, competencies, etc
            $table->text('description');
            $table->boolean('is_mandatory')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('job_profile_id')->references('id')->on('job_profiles')->onDelete('cascade');
            $table->index('job_profile_id');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_profile_requirements');
    }
};
