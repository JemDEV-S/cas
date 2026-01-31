<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si existe la clave foránea antes de intentar eliminarla
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'eligibility_overrides'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME LIKE '%application_id%'
        ");

        if (!empty($foreignKeys)) {
            Schema::table('eligibility_overrides', function (Blueprint $table) {
                $table->dropForeign(['application_id']);
            });
        }

        // Verificar si existe el índice único antes de intentar eliminarlo
        $uniqueIndexes = DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'eligibility_overrides'
            AND COLUMN_NAME = 'application_id'
            AND NON_UNIQUE = 0
            AND INDEX_NAME != 'PRIMARY'
        ");

        if (!empty($uniqueIndexes)) {
            Schema::table('eligibility_overrides', function (Blueprint $table) {
                $table->dropUnique(['application_id']);
            });
        }

        // Verificar si ya existe el índice normal antes de crearlo
        $normalIndexes = DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'eligibility_overrides'
            AND COLUMN_NAME = 'application_id'
            AND NON_UNIQUE = 1
            AND INDEX_NAME = 'eligibility_overrides_application_id_index'
        ");

        if (empty($normalIndexes)) {
            Schema::table('eligibility_overrides', function (Blueprint $table) {
                $table->index('application_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar si existe el índice normal antes de eliminarlo
        $normalIndexes = DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'eligibility_overrides'
            AND COLUMN_NAME = 'application_id'
            AND NON_UNIQUE = 1
        ");

        if (!empty($normalIndexes)) {
            Schema::table('eligibility_overrides', function (Blueprint $table) {
                $table->dropIndex(['application_id']);
            });
        }

        // Restaurar índice único y clave foránea
        Schema::table('eligibility_overrides', function (Blueprint $table) {
            $table->foreignUuid('application_id')
                ->unique()
                ->constrained('applications')
                ->onDelete('cascade');
        });
    }
};
