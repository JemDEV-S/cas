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
        Schema::create('config_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('system_config_id')->comment('ID de la configuración');
            $table->text('old_value')->nullable()->comment('Valor anterior');
            $table->text('new_value')->nullable()->comment('Valor nuevo');
            $table->uuid('changed_by')->comment('Usuario que realizó el cambio');
            $table->timestamp('changed_at')->comment('Fecha del cambio');
            $table->text('change_reason')->nullable()->comment('Razón del cambio');
            $table->inet('ip_address')->nullable()->comment('IP del usuario');
            $table->jsonb('metadata')->nullable()->comment('Metadatos adicionales');
            $table->timestamps();

            $table->foreign('system_config_id')
                ->references('id')
                ->on('system_configs')
                ->onDelete('cascade');

            $table->foreign('changed_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->index('system_config_id');
            $table->index('changed_by');
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_history');
    }
};
