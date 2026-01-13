<?php

namespace Modules\Evaluation\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder para permisos del módulo de Evaluación
 */
class EvaluationPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Ejecutar evaluaciones automáticas
            [
                'name' => 'Ejecutar Evaluaciones Automáticas',
                'slug' => 'evaluations.execute.automatic',
                'module' => 'evaluation',
                'description' => 'Ejecutar el proceso de evaluación automática de elegibilidad (Fase 4) para las postulaciones de una convocatoria',
            ],

            // Ver evaluaciones automáticas
            [
                'name' => 'Ver Evaluaciones Automáticas',
                'slug' => 'evaluations.view.automatic',
                'module' => 'evaluation',
                'description' => 'Ver el listado y detalles de evaluaciones automáticas ejecutadas',
            ],

            // Reejecutar evaluaciones automáticas
            [
                'name' => 'Reejecutar Evaluaciones Automáticas',
                'slug' => 'evaluations.reexecute.automatic',
                'module' => 'evaluation',
                'description' => 'Reejecutar evaluaciones automáticas que ya fueron procesadas (forzar reevaluación)',
            ],

            // Ver criterios de evaluación
            [
                'name' => 'Ver Criterios de Evaluación',
                'slug' => 'evaluations.view.criteria',
                'module' => 'evaluation',
                'description' => 'Ver los criterios de evaluación configurados por fase',
            ],

            // Gestionar criterios de evaluación
            [
                'name' => 'Gestionar Criterios de Evaluación',
                'slug' => 'evaluations.manage.criteria',
                'module' => 'evaluation',
                'description' => 'Crear, editar y eliminar criterios de evaluación (no incluye criterios del sistema)',
            ],

            // Gestión completa de evaluaciones
            [
                'name' => 'Gestión Completa de Evaluaciones',
                'slug' => 'evaluations.manage.all',
                'module' => 'evaluation',
                'description' => 'Acceso completo a todas las funcionalidades del módulo de evaluación',
            ],
        ];

        // Verificar si existe una tabla de permisos en el sistema
        // Opción 1: Si usas Spatie Laravel Permission
        if (class_exists('\Spatie\Permission\Models\Permission')) {
            foreach ($permissions as $permissionData) {
                \Spatie\Permission\Models\Permission::firstOrCreate(
                    ['name' => $permissionData['slug']],
                    [
                        'guard_name' => 'web',
                        'description' => $permissionData['description'],
                    ]
                );
            }
            $this->command->info('✅ Permisos de Evaluación creados/actualizados usando Spatie Permission');
        }
        // Opción 2: Si tienes un modelo Permission personalizado
        elseif (class_exists('\App\Models\Permission')) {
            foreach ($permissions as $permissionData) {
                \App\Models\Permission::updateOrCreate(
                    ['slug' => $permissionData['slug']],
                    $permissionData
                );
            }
            $this->command->info('✅ Permisos de Evaluación creados/actualizados usando modelo Permission personalizado');
        }
        // Opción 3: Log para información
        else {
            $this->command->warn('⚠️  No se detectó sistema de permisos. Los siguientes permisos deben agregarse manualmente:');
            foreach ($permissions as $permission) {
                $this->command->line("  - {$permission['slug']}: {$permission['name']}");
            }
        }
    }
}
