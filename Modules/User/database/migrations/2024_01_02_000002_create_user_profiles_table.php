<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('birth_date')->nullable();
            $table->string('gender', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->string('department')->nullable();
            $table->text('biography')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
