<?php

namespace Modules\Organization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Organization\Entities\OrganizationalUnit;
use Illuminate\Support\Facades\DB;

class OrganizationalStructureSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Limpiar tablas sin transacción explícita
            $this->cleanTables();

            // Crear estructura organizacional
            $this->createOrganizationalStructure();

            // Reconstruir closure table
            $this->rebuildClosureTable();

            $this->command->info('✓ Estructura organizacional creada exitosamente');
        } catch (\Exception $e) {
            $this->command->error('Error al crear estructura: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Limpiar tablas
     */
    private function cleanTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('organizational_unit_closure')->truncate();
        DB::table('organizational_units')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('→ Tablas limpiadas');
    }

    /**
     * Reconstruir closure table
     */
    private function rebuildClosureTable(): void
    {
        // Limpiar closure table
        DB::table('organizational_unit_closure')->truncate();

        // Obtener todas las unidades
        $units = OrganizationalUnit::all();

        foreach ($units as $unit) {
            // Auto-referencia
            DB::table('organizational_unit_closure')->insert([
                'ancestor_id' => $unit->id,
                'descendant_id' => $unit->id,
                'depth' => 0,
            ]);

            // Relaciones con ancestros
            if ($unit->parent_id) {
                $ancestors = DB::table('organizational_unit_closure')
                    ->where('descendant_id', $unit->parent_id)
                    ->get();

                foreach ($ancestors as $ancestor) {
                    DB::table('organizational_unit_closure')->insert([
                        'ancestor_id' => $ancestor->ancestor_id,
                        'descendant_id' => $unit->id,
                        'depth' => $ancestor->depth + 1,
                    ]);
                }
            }
        }

        $this->command->info('→ Closure table reconstruida');
    }

    private function createOrganizationalStructure(): void
    {
        // 1. Órganos de Alta Dirección
        $altaDireccion = OrganizationalUnit::create([
            'code' => 'OAD',
            'name' => 'Órganos de Alta Dirección',
            'description' => 'Órganos encargados de la dirección estratégica de la entidad',
            'type' => 'organo',
            'parent_id' => null,
            'level' => 0,
            'path' => 'OAD',
            'order' => 1,
            'is_active' => true,
        ]);

        $direccionGeneral = OrganizationalUnit::create([
            'code' => 'DG',
            'name' => 'Dirección General',
            'description' => 'Máximo órgano ejecutivo de la entidad',
            'type' => 'organo',
            'parent_id' => $altaDireccion->id,
            'level' => 1,
            'path' => 'OAD/DG',
            'order' => 1,
            'is_active' => true,
        ]);

        $secretariaGeneral = OrganizationalUnit::create([
            'code' => 'SG',
            'name' => 'Secretaría General',
            'description' => 'Responsable de la gestión administrativa',
            'type' => 'organo',
            'parent_id' => $altaDireccion->id,
            'level' => 1,
            'path' => 'OAD/SG',
            'order' => 2,
            'is_active' => true,
        ]);

        // 2. Órganos de Control
        $control = OrganizationalUnit::create([
            'code' => 'OC',
            'name' => 'Órgano de Control Institucional',
            'description' => 'Encargado del control interno',
            'type' => 'organo',
            'parent_id' => null,
            'level' => 0,
            'path' => 'OC',
            'order' => 2,
            'is_active' => true,
        ]);

        // 3. Órganos de Asesoramiento
        $asesoria = OrganizationalUnit::create([
            'code' => 'OA',
            'name' => 'Órganos de Asesoramiento',
            'description' => 'Brindan asesoramiento especializado',
            'type' => 'organo',
            'parent_id' => null,
            'level' => 0,
            'path' => 'OA',
            'order' => 3,
            'is_active' => true,
        ]);

        $oficinaPlanificacion = OrganizationalUnit::create([
            'code' => 'OPP',
            'name' => 'Oficina de Planificación y Presupuesto',
            'description' => 'Planificación estratégica y presupuestal',
            'type' => 'area',
            'parent_id' => $asesoria->id,
            'level' => 1,
            'path' => 'OA/OPP',
            'order' => 1,
            'is_active' => true,
        ]);

        $oficinaAsesoriaJuridica = OrganizationalUnit::create([
            'code' => 'OAJ',
            'name' => 'Oficina de Asesoría Jurídica',
            'description' => 'Asesoramiento legal',
            'type' => 'area',
            'parent_id' => $asesoria->id,
            'level' => 1,
            'path' => 'OA/OAJ',
            'order' => 2,
            'is_active' => true,
        ]);

        // 4. Órganos de Apoyo
        $apoyo = OrganizationalUnit::create([
            'code' => 'OAPP',
            'name' => 'Órganos de Apoyo',
            'description' => 'Soporte a la gestión institucional',
            'type' => 'organo',
            'parent_id' => null,
            'level' => 0,
            'path' => 'OAPP',
            'order' => 4,
            'is_active' => true,
        ]);

        $oficinaAdministracion = OrganizationalUnit::create([
            'code' => 'OAD-ADM',
            'name' => 'Oficina de Administración',
            'description' => 'Gestión administrativa y financiera',
            'type' => 'area',
            'parent_id' => $apoyo->id,
            'level' => 1,
            'path' => 'OAPP/OAD-ADM',
            'order' => 1,
            'is_active' => true,
        ]);

        // Sub-unidades de Administración
        OrganizationalUnit::create([
            'code' => 'RRHH',
            'name' => 'Unidad de Recursos Humanos',
            'description' => 'Gestión del personal',
            'type' => 'sub_unidad',
            'parent_id' => $oficinaAdministracion->id,
            'level' => 2,
            'path' => 'OAPP/OAD-ADM/RRHH',
            'order' => 1,
            'is_active' => true,
        ]);

        OrganizationalUnit::create([
            'code' => 'LOG',
            'name' => 'Unidad de Logística',
            'description' => 'Abastecimiento y logística',
            'type' => 'sub_unidad',
            'parent_id' => $oficinaAdministracion->id,
            'level' => 2,
            'path' => 'OAPP/OAD-ADM/LOG',
            'order' => 2,
            'is_active' => true,
        ]);

        OrganizationalUnit::create([
            'code' => 'CONT',
            'name' => 'Unidad de Contabilidad',
            'description' => 'Gestión contable',
            'type' => 'sub_unidad',
            'parent_id' => $oficinaAdministracion->id,
            'level' => 2,
            'path' => 'OAPP/OAD-ADM/CONT',
            'order' => 3,
            'is_active' => true,
        ]);

        $oficinaTecnologia = OrganizationalUnit::create([
            'code' => 'OTI',
            'name' => 'Oficina de Tecnologías de la Información',
            'description' => 'Gestión de tecnología',
            'type' => 'area',
            'parent_id' => $apoyo->id,
            'level' => 1,
            'path' => 'OAPP/OTI',
            'order' => 2,
            'is_active' => true,
        ]);

        // 5. Órganos de Línea
        $linea = OrganizationalUnit::create([
            'code' => 'OL',
            'name' => 'Órganos de Línea',
            'description' => 'Ejecutan las funciones sustantivas',
            'type' => 'organo',
            'parent_id' => null,
            'level' => 0,
            'path' => 'OL',
            'order' => 5,
            'is_active' => true,
        ]);

        $direccionOperaciones = OrganizationalUnit::create([
            'code' => 'DO',
            'name' => 'Dirección de Operaciones',
            'description' => 'Gestión operativa',
            'type' => 'area',
            'parent_id' => $linea->id,
            'level' => 1,
            'path' => 'OL/DO',
            'order' => 1,
            'is_active' => true,
        ]);

        $direccionDesarrollo = OrganizationalUnit::create([
            'code' => 'DD',
            'name' => 'Dirección de Desarrollo',
            'description' => 'Proyectos y desarrollo',
            'type' => 'area',
            'parent_id' => $linea->id,
            'level' => 1,
            'path' => 'OL/DD',
            'order' => 2,
            'is_active' => true,
        ]);

        $this->command->info('→ Unidades organizacionales creadas');
    }
}
