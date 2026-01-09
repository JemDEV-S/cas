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
        Schema::create('result_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relaciones
            $table->foreignUuid('result_publication_id')
                ->constrained('result_publications')
                ->cascadeOnDelete()
                ->comment('Publicación de resultados');

            // Datos del archivo
            $table->enum('format', ['excel', 'csv', 'pdf'])
                ->comment('Formato del archivo exportado');

            $table->string('file_path')->comment('Ruta del archivo en storage');
            $table->string('file_name')->comment('Nombre original del archivo');
            $table->bigInteger('file_size')->nullable()->comment('Tamaño en bytes');
            $table->integer('rows_count')->nullable()->comment('Cantidad de filas exportadas');

            // Auditoría
            $table->foreignUuid('exported_by')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Usuario que generó la exportación');

            $table->timestamp('exported_at')->useCurrent()->comment('Fecha y hora de exportación');

            // Metadata
            $table->json('metadata')->nullable()->comment('Información adicional de la exportación');

            $table->timestamps();

            // Índices
            $table->index('result_publication_id');
            $table->index('exported_by');
            $table->index('exported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_exports');
    }
};
