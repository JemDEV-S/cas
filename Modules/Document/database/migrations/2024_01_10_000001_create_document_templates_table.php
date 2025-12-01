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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique()->comment('Código único del template');
            $table->string('name')->comment('Nombre del template');
            $table->text('description')->nullable();
            $table->string('category', 50)->comment('perfil, convocatoria, acta, etc.');

            // Contenido del template
            $table->longText('content')->comment('Contenido HTML/Blade del template');
            $table->json('variables')->nullable()->comment('Variables disponibles en el template');

            // Configuración de firma
            $table->boolean('signature_required')->default(false);
            $table->json('signature_positions')->nullable()->comment('Posiciones de firma en el PDF');
            $table->string('signature_workflow_type', 20)->default('sequential')->comment('sequential o parallel');
            $table->json('signers_config')->nullable()->comment('Configuración de firmantes');

            // Configuración de página
            $table->string('paper_size', 20)->default('A4')->comment('A4, Letter, etc.');
            $table->string('orientation', 20)->default('portrait')->comment('portrait o landscape');
            $table->json('margins')->nullable()->comment('Márgenes del documento');
            $table->text('header_content')->nullable();
            $table->text('footer_content')->nullable();
            $table->string('watermark')->nullable();

            // Estado y auditoría
            $table->string('status', 20)->default('active')->comment('active, inactive, draft');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('code');
            $table->index('category');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
