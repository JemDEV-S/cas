<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('title');
            $table->uuid('organizational_unit_id');
            $table->string('job_level')->nullable();
            $table->string('contract_type', 50);
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('mission')->nullable();
            $table->text('working_conditions')->nullable();
            $table->string('status', 50)->default('draft');

            $table->uuid('requested_by')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organizational_unit_id')->references('id')->on('organizational_units');
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');

            $table->index('code');
            $table->index('status');
            $table->index('organizational_unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_profiles');
    }
};
