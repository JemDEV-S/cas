<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\Auth\Entities\Role;

class AdminRRHHSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'admin-rrhh')->first();
        if (!$role) {
            $this->command->warn('❌ Rol admin-rrhh no encontrado');
            return;
        }

        User::updateOrCreate(
            ['dni' => '72306843'],
            [
                'email' => 'rrhh@cas.com',
                'first_name' => 'Carlos',
                'last_name' => 'Mendoza',
                'password' => Hash::make('password'),
                'phone' => '987654321',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->syncRoles([$role->id]);

        $this->command->info('✅ Admin RRHH creado: 72306843 / password');
    }
}
