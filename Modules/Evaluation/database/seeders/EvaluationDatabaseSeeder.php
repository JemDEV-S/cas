<?php

namespace Modules\Evaluation\Database\Seeders;

use Illuminate\Database\Seeder;

class EvaluationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            EvaluationCriteriaSeeder::class,
        ]);
        
        $this->command->info('✅ Evaluation Module seeded successfully!');
    }
}