<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\Auth\Entities\Role;

class AreaUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'area-user')->first();
        if (!$role) {
            $this->command->warn('❌ Rol area-user no encontrado');
            return;
        }

        User::updateOrCreate(
            ['dni' => '76543210'],
            [
                'email' => 'area@cas.com',
                'first_name' => 'Lucía',
                'last_name' => 'Fernández',
                'password' => Hash::make('password'),
                'phone' => '912345678',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->syncRoles([$role->id]);

        $this->command->info('✅ Usuario de Área creado: 76543210 / password');
    }
}
