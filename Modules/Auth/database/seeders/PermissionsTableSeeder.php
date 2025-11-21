<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Entities\Permission;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Auth Module Permissions
            ['name' => 'Ver Roles', 'slug' => 'auth.view.roles', 'module' => 'auth'],
            ['name' => 'Ver Rol', 'slug' => 'auth.view.role', 'module' => 'auth'],
            ['name' => 'Crear Rol', 'slug' => 'auth.create.role', 'module' => 'auth'],
            ['name' => 'Actualizar Rol', 'slug' => 'auth.update.role', 'module' => 'auth'],
            ['name' => 'Eliminar Rol', 'slug' => 'auth.delete.role', 'module' => 'auth'],
            ['name' => 'Ver Permisos', 'slug' => 'auth.view.permissions', 'module' => 'auth'],
            ['name' => 'Ver Permiso', 'slug' => 'auth.view.permission', 'module' => 'auth'],
            ['name' => 'Crear Permiso', 'slug' => 'auth.create.permission', 'module' => 'auth'],
            ['name' => 'Actualizar Permiso', 'slug' => 'auth.update.permission', 'module' => 'auth'],
            ['name' => 'Eliminar Permiso', 'slug' => 'auth.delete.permission', 'module' => 'auth'],

            // User Module Permissions
            ['name' => 'Ver Usuarios', 'slug' => 'user.view.users', 'module' => 'user'],
            ['name' => 'Ver Usuario', 'slug' => 'user.view.user', 'module' => 'user'],
            ['name' => 'Crear Usuario', 'slug' => 'user.create.user', 'module' => 'user'],
            ['name' => 'Actualizar Usuario', 'slug' => 'user.update.user', 'module' => 'user'],
            ['name' => 'Eliminar Usuario', 'slug' => 'user.delete.user', 'module' => 'user'],

            // JobPosting Module Permissions
            ['name' => 'Ver Convocatorias', 'slug' => 'jobposting.view.convocatorias', 'module' => 'jobposting'],
            ['name' => 'Crear Convocatoria', 'slug' => 'jobposting.create.convocatoria', 'module' => 'jobposting'],
            ['name' => 'Actualizar Convocatoria', 'slug' => 'jobposting.update.convocatoria', 'module' => 'jobposting'],
            ['name' => 'Eliminar Convocatoria', 'slug' => 'jobposting.delete.convocatoria', 'module' => 'jobposting'],
            ['name' => 'Publicar Convocatoria', 'slug' => 'jobposting.publish.convocatoria', 'module' => 'jobposting'],

            // Application Module Permissions
            ['name' => 'Ver Postulaciones', 'slug' => 'application.view.postulaciones', 'module' => 'application'],
            ['name' => 'Postular', 'slug' => 'application.create.postulacion', 'module' => 'application'],
            ['name' => 'Actualizar Postulación', 'slug' => 'application.update.postulacion', 'module' => 'application'],

            // Evaluation Module Permissions
            ['name' => 'Ver Evaluaciones', 'slug' => 'evaluation.view.evaluaciones', 'module' => 'evaluation'],
            ['name' => 'Evaluar Postulante', 'slug' => 'evaluation.create.calificacion', 'module' => 'evaluation'],
            ['name' => 'Actualizar Evaluación', 'slug' => 'evaluation.update.calificacion', 'module' => 'evaluation'],

            // Reporting Module Permissions
            ['name' => 'Ver Reportes', 'slug' => 'reporting.view.reportes', 'module' => 'reporting'],
            ['name' => 'Exportar Reportes', 'slug' => 'reporting.export.reporte', 'module' => 'reporting'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['slug' => $permissionData['slug']],
                array_merge($permissionData, [
                    'description' => null,
                    'is_active' => true,
                ])
            );
        }
    }
}
