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
        Schema::create('application_knowledge', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained('applications')->onDelete('cascade');

            // Conocimiento declarado
            $table->string('knowledge_name')->comment('Sistema, programa, legislación, etc');
            $table->string('proficiency_level', 50)->nullable()->comment('BASICO, INTERMEDIO, AVANZADO');

            $table->timestamps();

            // Índices
            $table->index('application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_knowledge');
    }
};
