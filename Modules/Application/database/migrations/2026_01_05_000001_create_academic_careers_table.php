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
        Schema::create('academic_careers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique()->comment('Código único de la carrera, ej: CAR_ADMINISTRACION');
            $table->string('name', 200)->comment('Nombre de la carrera');
            $table->string('short_name', 100)->nullable()->comment('Nombre corto para UI');
            $table->string('sunedu_category', 100)->nullable()->comment('Categoría SUNEDU');
            $table->string('category_group', 100)->nullable()->comment('Agrupación propia para UI');
            $table->boolean('requires_colegiatura')->default(false)->comment('True para carreras colegiadas');
            $table->text('description')->nullable()->comment('Descripción opcional');
            $table->integer('display_order')->default(999)->comment('Orden de visualización en SELECT');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('code');
            $table->index('sunedu_category');
            $table->index('category_group');
            $table->index('is_active');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_careers');
    }
};
