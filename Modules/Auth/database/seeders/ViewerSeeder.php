<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\Auth\Entities\Role;

class ViewerSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'viewer')->first();
        if (!$role) {
            $this->command->warn('❌ Rol viewer no encontrado');
            return;
        }

        User::updateOrCreate(
            ['dni' => '32109876'],
            [
                'email' => 'viewer@cas.com',
                'first_name' => 'Laura',
                'last_name' => 'Gómez',
                'password' => Hash::make('password'),
                'phone' => '944556677',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->syncRoles([$role->id]);

        $this->command->info('✅ Visualizador creado: 32109876 / password');
    }
}
