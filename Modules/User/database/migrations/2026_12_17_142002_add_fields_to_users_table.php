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
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender', 20)->nullable()->after('last_name');
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('address')->nullable()->after('birth_date');
            $table->string('district')->nullable()->after('address');
            $table->string('province')->nullable()->after('district');
            $table->string('department')->nullable()->after('province');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gender', 'birth_date','address','district','province','department');
        });
    }
};
