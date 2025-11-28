<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\Auth\Entities\Role;

class JurySeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'jury')->first();
        if (!$role) {
            $this->command->warn('❌ Rol jury no encontrado');
            return;
        }

        User::updateOrCreate(
            ['dni' => '54321098'],
            [
                'email' => 'jury@cas.com',
                'first_name' => 'Ana',
                'last_name' => 'Ríos',
                'password' => Hash::make('password'),
                'phone' => '987123456',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->syncRoles([$role->id]);

        $this->command->info('✅ Jurado creado: 54321098 / password');
    }
}
