<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Entities\Role;
use Modules\Auth\Entities\Permission;

class AuthDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            RolePermissionsSeeder::class,
            SuperAdminSeeder::class,
            // AdminRRHHSeeder::class,
            // AreaUserSeeder::class,
            // RRHHReviewerSeeder::class,
            // JurySeeder::class,
            // ApplicantSeeder::class,
            // ViewerSeeder::class,
        ]);
    }
}
