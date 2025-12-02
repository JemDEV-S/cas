<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Actualizar position_codes
        Schema::table('position_codes', function (Blueprint $table) {
            $table->json('education_levels_accepted')->nullable()->after('description');
        });

        DB::table('position_codes')->get()->each(function ($positionCode) {
            $oldLevel = $positionCode->education_level_required;
            if ($oldLevel) {
                DB::table('position_codes')
                    ->where('id', $positionCode->id)
                    ->update(['education_levels_accepted' => json_encode([$oldLevel])]);
            }
        });

        // 2. Actualizar job_profiles
        Schema::table('job_profiles', function (Blueprint $table) {
            $table->json('education_levels')->nullable()->after('working_conditions');
        });

        DB::table('job_profiles')->get()->each(function ($jobProfile) {
            $oldLevel = $jobProfile->education_level;
            if ($oldLevel) {
                DB::table('job_profiles')
                    ->where('id', $jobProfile->id)
                    ->update(['education_levels' => json_encode([$oldLevel])]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_profiles', function (Blueprint $table) {
            $table->dropColumn('education_levels');
        });

        Schema::table('position_codes', function (Blueprint $table) {
            $table->dropColumn('education_levels_accepted');
        });
    }
};
