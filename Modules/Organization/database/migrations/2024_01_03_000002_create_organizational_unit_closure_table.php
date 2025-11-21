<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizational_unit_closure', function (Blueprint $table) {
            $table->uuid('ancestor_id');
            $table->uuid('descendant_id');
            $table->integer('depth');

            $table->foreign('ancestor_id')
                ->references('id')
                ->on('organizational_units')
                ->onDelete('cascade');

            $table->foreign('descendant_id')
                ->references('id')
                ->on('organizational_units')
                ->onDelete('cascade');

            $table->primary(['ancestor_id', 'descendant_id']);
            $table->index('depth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizational_unit_closure');
    }
};
