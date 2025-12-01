<?php

namespace Modules\Organization\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationRolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
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

        $orgPermissions = DB::table('permissions')
            ->where('module', 'organization')
            ->pluck('id', 'name');

        if ($orgPermissions->isEmpty()) {
            $this->command->warn('⚠ No se encontraron permisos del módulo Organization');
            return;
        }

        $read = [
            'organization.view.units',
            'organization.view.unit',
            'organization.view.tree',
        ];

        $manager = [
            ...$read,
            'organization.create.unit',
            'organization.update.unit',
            'organization.move.unit',
            'organization.export.data',
        ];

        foreach ($roles as $role) {
            $roleName = strtolower($role->name);
            $toAssign = [];

            if (str_contains($roleName, 'admin')) {
                $toAssign = $orgPermissions->keys()->toArray();
            } elseif (str_contains($roleName, 'jefe') || str_contains($roleName, 'gerente')) {
                $toAssign = $manager;
            } else {
                $toAssign = $read;
            }

            $assigned = 0;

            foreach ($toAssign as $perm) {
                if (!isset($orgPermissions[$perm])) continue;

                $exists = DB::table('role_permission')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $orgPermissions[$perm])
                    ->exists();

                if (!$exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $orgPermissions[$perm],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $assigned++;
                }
            }

            $this->command->info("✓ Rol {$role->name}: {$assigned} permisos asignados");
        }

        $this->command->info('✅ Asignación de permisos completada');
    }
}
