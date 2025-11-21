<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('language', 5)->default('es');
            $table->string('timezone')->default('America/Lima');
            $table->boolean('notifications_email')->default(true);
            $table->boolean('notifications_system')->default(true);
            $table->string('theme', 20)->default('light');
            $table->string('date_format', 20)->default('d/m/Y');
            $table->jsonb('preferences')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
