<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_profile_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_profile_id');
            $table->uuid('user_id')->nullable()->comment('Usuario que realizó el cambio');

            $table->string('action', 100)->comment('Acción realizada: created, updated, submitted, approved, etc');
            $table->string('from_status', 50)->nullable()->comment('Estado anterior');
            $table->string('to_status', 50)->nullable()->comment('Estado nuevo');

            $table->text('description')->nullable()->comment('Descripción del cambio');
            $table->jsonb('changes')->nullable()->comment('Detalles de los cambios realizados');
            $table->ipAddress('ip_address')->nullable();

            $table->timestamps();

            $table->foreign('job_profile_id')->references('id')->on('job_profiles')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->index('job_profile_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_profile_history');
    }
};
