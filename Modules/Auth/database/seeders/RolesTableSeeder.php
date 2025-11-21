<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Entities\Role;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Administrador',
                'slug' => 'super-admin',
                'description' => 'Control total del sistema',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Administrador RRHH',
                'slug' => 'admin-rrhh',
                'description' => 'Gestión de convocatorias y procesos de RRHH',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Usuario de Área',
                'slug' => 'area-user',
                'description' => 'Solicita perfiles de puesto',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Revisor RRHH',
                'slug' => 'rrhh-reviewer',
                'description' => 'Revisa y aprueba perfiles de puesto',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Jurado',
                'slug' => 'jury',
                'description' => 'Evalúa postulaciones de candidatos',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Postulante',
                'slug' => 'applicant',
                'description' => 'Postula a convocatorias',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Visualizador',
                'slug' => 'viewer',
                'description' => 'Solo visualización de información',
                'is_system' => true,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}
