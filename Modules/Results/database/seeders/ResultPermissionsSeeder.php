<?php

namespace Modules\Results\Database\Seeders;

use Illuminate\Database\Seeder;

class ResultPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Ver publicaciones de resultados
            [
                'name' => 'Ver Publicaciones de Resultados',
                'slug' => 'results.view',
                'module' => 'results',
                'description' => 'Ver listado y detalle de publicaciones de resultados',
            ],

            // Publicar resultados Fase 4
            [
                'name' => 'Publicar Resultados Fase 4 (Elegibilidad)',
                'slug' => 'results.publish.phase4',
                'module' => 'results',
                'description' => 'Publicar resultados de evaluación de requisitos mínimos (APTO/NO APTO)',
            ],

            // Procesar resultados CV
            [
                'name' => 'Procesar Resultados CV',
                'slug' => 'results.process.cv',
                'module' => 'results',
                'description' => 'Procesar y transferir puntajes de evaluación curricular',
            ],

            // Publicar resultados Fase 7
            [
                'name' => 'Publicar Resultados Fase 7 (Evaluación Curricular)',
                'slug' => 'results.publish.phase7',
                'module' => 'results',
                'description' => 'Publicar resultados de evaluación curricular con ranking',
            ],

            // Publicar resultados Fase 9
            [
                'name' => 'Publicar Resultados Fase 9 (Resultados Finales)',
                'slug' => 'results.publish.phase9',
                'module' => 'results',
                'description' => 'Publicar resultados finales del proceso de selección',
            ],

            // Despublicar resultados
            [
                'name' => 'Despublicar Resultados',
                'slug' => 'results.unpublish',
                'module' => 'results',
                'description' => 'Despublicar resultados (solo antes de firmar)',
            ],

            // Republicar resultados
            [
                'name' => 'Republicar Resultados',
                'slug' => 'results.republish',
                'module' => 'results',
                'description' => 'Republicar resultados previamente despublicados',
            ],

            // Descargar documentos
            [
                'name' => 'Descargar Documentos de Resultados',
                'slug' => 'results.download',
                'module' => 'results',
                'description' => 'Descargar PDF y Excel de resultados',
            ],

            // Generar Excel
            [
                'name' => 'Generar Exportaciones Excel',
                'slug' => 'results.export.excel',
                'module' => 'results',
                'description' => 'Generar o regenerar archivos Excel de resultados',
            ],

            // Configurar firmantes
            [
                'name' => 'Configurar Jurados Firmantes',
                'slug' => 'results.configure.signers',
                'module' => 'results',
                'description' => 'Seleccionar jurados que deben firmar los documentos',
            ],

            // Gestión completa (super admin)
            [
                'name' => 'Gestión Completa de Resultados',
                'slug' => 'results.manage.all',
                'module' => 'results',
                'description' => 'Acceso completo a todas las funcionalidades de resultados',
            ],
        ];

        // Verificar si existe una tabla de permisos en el sistema
        // Este código debe adaptarse según tu implementación actual de permisos

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
            $this->command->info('Permisos creados/actualizados usando Spatie Permission');
        }
        // Opción 2: Si tienes un modelo Permission personalizado
        elseif (class_exists('\App\Models\Permission')) {
            foreach ($permissions as $permissionData) {
                \App\Models\Permission::updateOrCreate(
                    ['slug' => $permissionData['slug']],
                    $permissionData
                );
            }
            $this->command->info('Permisos creados/actualizados usando modelo Permission personalizado');
        }
        // Opción 3: Log para información
        else {
            $this->command->warn('No se detectó sistema de permisos. Los siguientes permisos deben agregarse manualmente:');
            foreach ($permissions as $permission) {
                $this->command->line("  - {$permission['slug']}: {$permission['name']}");
            }
        }
    }
}
