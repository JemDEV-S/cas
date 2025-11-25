<?php

namespace Modules\Organization\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationRolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los roles disponibles
        $roles = DB::table('roles')->get();

        if ($roles->isEmpty()) {
            $this->command->warn('⚠ No se encontraron roles en la base de datos');
            $this->command->info('→ Crea roles primero con: php artisan db:seed --class="Modules\Auth\Database\Seeders\RolesTableSeeder"');
            return;
        }

        $this->command->info('Roles disponibles:');
        foreach ($roles as $role) {
            $this->command->line("  - {$role->name}");
        }

        // Obtener permisos del módulo Organization
        $orgPermissions = DB::table('permissions')
            ->where('name', 'like', 'organization.%')
            ->pluck('id', 'name');

        if ($orgPermissions->isEmpty()) {
            $this->command->warn('⚠ No se encontraron permisos del módulo Organization');
            return;
        }

        // Permisos de solo lectura
        $readPermissions = [
            'organization.view.units',
            'organization.view.unit',
            'organization.view.tree',
        ];

        // Permisos para manager
        $managerPermissions = [
            'organization.view.units',
            'organization.view.unit',
            'organization.view.tree',
            'organization.create.unit',
            'organization.update.unit',
            'organization.move.unit',
            'organization.export.data',
        ];

        // Asignar permisos según el rol
        foreach ($roles as $role) {
            $roleName = strtolower($role->name);
            $permissionsToAssign = [];

            // Determinar qué permisos asignar según el nombre del rol
            if (in_array($roleName, ['admin', 'administrador', 'administrator'])) {
                // Admin: todos los permisos
                $permissionsToAssign = $orgPermissions->keys()->toArray();
            } elseif (in_array($roleName, ['manager', 'gerente', 'jefe'])) {
                // Manager: crear, leer, actualizar
                $permissionsToAssign = $managerPermissions;
            } elseif (in_array($roleName, ['user', 'usuario', 'empleado'])) {
                // User: solo lectura
                $permissionsToAssign = $readPermissions;
            } else {
                // Otros roles: solo lectura por defecto
                $permissionsToAssign = $readPermissions;
            }

            // Asignar permisos
            $assigned = 0;
            foreach ($permissionsToAssign as $permissionName) {
                if (isset($orgPermissions[$permissionName])) {
                    $permissionId = $orgPermissions[$permissionName];

                    // Verificar si ya existe la relación
                    $exists = DB::table('role_permission')
                        ->where('role_id', $role->id)
                        ->where('permission_id', $permissionId)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_permission')->insert([
                            'role_id' => $role->id,
                            'permission_id' => $permissionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $assigned++;
                    }
                }
            }

            if ($assigned > 0) {
                $this->command->info("✓ Asignados {$assigned} permisos al rol: {$role->name}");
            } else {
                $this->command->line("  Ya tenía permisos asignados: {$role->name}");
            }
        }

        $this->command->info('✓ Proceso de asignación completado');
    }
}
