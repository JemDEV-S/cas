<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\Auth\Entities\Role;

class RRHHReviewerSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'rrhh-reviewer')->first();
        if (!$role) {
            $this->command->warn('❌ Rol rrhh-reviewer no encontrado');
            return;
        }

        User::updateOrCreate(
            ['dni' => '65432109'],
            [
                'email' => 'reviewer@cas.com',
                'first_name' => 'Mario',
                'last_name' => 'Torres',
                'password' => Hash::make('password'),
                'phone' => '998877665',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->syncRoles([$role->id]);

        $this->command->info('✅ Revisor RRHH creado: 65432109 / password');
    }
}
