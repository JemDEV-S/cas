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
        Schema::create('system_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('config_group_id')->comment('ID del grupo de configuración');
            $table->string('key', 100)->unique()->comment('Clave única de configuración');
            $table->text('value')->nullable()->comment('Valor actual');
            $table->string('value_type', 20)->comment('Tipo de dato');
            $table->text('default_value')->nullable()->comment('Valor por defecto');
            $table->text('description')->nullable()->comment('Descripción');

            // Validación
            $table->jsonb('validation_rules')->nullable()->comment('Reglas de validación Laravel');
            $table->jsonb('options')->nullable()->comment('Opciones si es select/enum');
            $table->decimal('min_value', 15, 2)->nullable()->comment('Valor mínimo');
            $table->decimal('max_value', 15, 2)->nullable()->comment('Valor máximo');

            // UI
            $table->string('display_name', 150)->comment('Nombre para mostrar');
            $table->text('help_text')->nullable()->comment('Texto de ayuda');
            $table->integer('display_order')->default(0)->comment('Orden de visualización');
            $table->string('input_type', 20)->comment('Tipo de input en UI');

            // Permisos
            $table->boolean('is_public')->default(false)->comment('Visible sin autenticación');
            $table->string('required_permission', 100)->nullable()->comment('Permiso requerido');
            $table->boolean('is_editable')->default(true)->comment('Puede editarse desde UI');
            $table->boolean('is_system')->default(false)->comment('Config crítica del sistema');

            $table->jsonb('metadata')->nullable()->comment('Metadatos adicionales');
            $table->timestamps();

            $table->foreign('config_group_id')
                ->references('id')
                ->on('config_groups')
                ->onDelete('cascade');

            $table->index('config_group_id');
            $table->index('key');
            $table->index('is_public');
            $table->index('is_editable');
            $table->index('is_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configs');
    }
};
