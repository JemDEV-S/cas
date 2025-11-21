<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_profile_responsibilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_profile_id');
            $table->text('description');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('job_profile_id')->references('id')->on('job_profiles')->onDelete('cascade');
            $table->index('job_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_profile_responsibilities');
    }
};
