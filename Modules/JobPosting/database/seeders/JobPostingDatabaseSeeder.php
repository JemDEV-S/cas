<?php

namespace Modules\JobPosting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\JobPosting\Database\Seeders\ProcessPhasesSeeder;

class JobPostingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(ProcessPhasesSeeder::class);
    }
}
