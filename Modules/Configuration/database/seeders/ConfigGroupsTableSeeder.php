<?php

namespace Modules\Configuration\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Configuration\Entities\ConfigGroup;

class ConfigGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'code' => 'general',
                'name' => 'General',
                'description' => 'Configuración general del sistema',
                'icon' => 'settings',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'process',
                'name' => 'Proceso',
                'description' => 'Configuración de procesos de convocatoria',
                'icon' => 'briefcase',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'documents',
                'name' => 'Documentos',
                'description' => 'Configuración de gestión documental',
                'icon' => 'file-text',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'notifications',
                'name' => 'Notificaciones',
                'description' => 'Configuración de notificaciones',
                'icon' => 'bell',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'security',
                'name' => 'Seguridad',
                'description' => 'Configuración de seguridad',
                'icon' => 'shield',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'integrations',
                'name' => 'Integraciones',
                'description' => 'Configuración de integraciones externas',
                'icon' => 'link',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'code' => 'reports',
                'name' => 'Reportes',
                'description' => 'Configuración de reportes',
                'icon' => 'bar-chart',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'audit',
                'name' => 'Auditoría',
                'description' => 'Configuración de auditoría',
                'icon' => 'eye',
                'order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($groups as $group) {
            ConfigGroup::create($group);
        }
    }
}
