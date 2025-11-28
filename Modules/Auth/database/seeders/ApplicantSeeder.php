<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\Auth\Entities\Role;

class ApplicantSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'applicant')->first();
        if (!$role) {
            $this->command->warn('❌ Rol applicant no encontrado');
            return;
        }

        User::updateOrCreate(
            ['dni' => '43210987'],
            [
                'email' => 'applicant@cas.com',
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'password' => Hash::make('password'),
                'phone' => '955667788',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->syncRoles([$role->id]);

        $this->command->info('✅ Postulante creado: 43210987 / password');
    }
}
