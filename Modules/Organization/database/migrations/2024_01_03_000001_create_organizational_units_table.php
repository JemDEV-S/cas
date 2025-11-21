<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 50); // organo, area, sub_unidad
            $table->uuid('parent_id')->nullable();
            $table->integer('level')->default(1);
            $table->string('path')->nullable(); // /1/5/12
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('organizational_units')
                ->onDelete('restrict');

            $table->index('code');
            $table->index('type');
            $table->index('parent_id');
            $table->index('level');
            $table->index('path');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizational_units');
    }
};
