<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\Auth\Entities\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar o crear el rol super-admin
        $role = Role::where('slug', 'super-admin')->first();
        if (!$role) {
            $this->command->error('❌ Rol super-admin no encontrado. Ejecuta RolesTableSeeder primero.');
            return;
        }

        // Crear usuario superadmin
        $user = User::updateOrCreate(
            ['dni' => '12345678'],
            [
                'email' => 'admin@cas.com',
                'first_name' => 'Super',
                'last_name' => 'Administrador',
                'password' => Hash::make('password'),
                'phone' => null,
                'is_active' => true,
                'email_verified_at' => now(),
                'last_login_at' => null,
            ]
        );

        // Asignar rol
        $user->syncRoles([$role->id]);

        $this->command->info("✅ SuperAdmin creado:");
        $this->command->line("   DNI: 12345678");
        $this->command->line("   Email: admin@cas.com");
        $this->command->line("   Contraseña: password");
    }
}
