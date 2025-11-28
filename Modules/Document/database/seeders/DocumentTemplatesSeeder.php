<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Document\Entities\DocumentTemplate;

class DocumentTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Template de Perfil de Puesto
        DocumentTemplate::updateOrCreate(
            ['code' => 'TPL_JOB_PROFILE'],
            [
                'name' => 'Perfil de Puesto',
                'description' => 'Template oficial para documentos de perfiles de puesto aprobados',
                'category' => 'perfil',
                'content' => file_get_contents(__DIR__ . '/../../resources/views/templates/job_profile.blade.php'),
                'variables' => [
                    'title', 'code', 'profile_title', 'profile_name', 'position_code',
                    'organizational_unit', 'requesting_unit', 'job_level', 'contract_type',
                    'salary_range', 'work_regime', 'total_vacancies', 'mission',
                    'main_functions', 'education_level', 'career_field', 'title_required',
                    'colegiatura_required', 'general_experience_years', 'specific_experience_years',
                    'knowledge_areas', 'required_competencies', 'working_conditions',
                    'justification', 'requested_by', 'reviewed_by', 'approved_by',
                    'requested_at', 'reviewed_at', 'approved_at', 'generation_date',
                ],
                'signature_required' => true,
                'signature_workflow_type' => 'sequential',
                'signers_config' => [
                    [
                        'role_key' => 'approved_by',
                        'type' => 'aprobacion',
                        'role' => 'Área Aprobadora',
                    ],
                    [
                        'role_key' => 'requested_by',
                        'type' => 'visto_bueno',
                        'role' => 'Área Usuaria',
                    ],
                ],
                'signature_positions' => [
                    [
                        'page' => -1, // última página
                        'x' => 50,
                        'y' => 50,
                        'width' => 200,
                        'height' => 80,
                    ],
                    [
                        'page' => -1,
                        'x' => 350,
                        'y' => 50,
                        'width' => 200,
                        'height' => 80,
                    ],
                ],
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'margins' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                ],
                'status' => 'active',
            ]
        );

        // Puedes agregar más templates aquí
        // Template de Convocatoria
        DocumentTemplate::updateOrCreate(
            ['code' => 'TPL_CONVOCATORIA'],
            [
                'name' => 'Bases de Convocatoria',
                'description' => 'Template para documentos de convocatorias',
                'category' => 'convocatoria',
                'content' => '<h1>Template de Convocatoria</h1><p>Por implementar</p>',
                'variables' => [],
                'signature_required' => true,
                'status' => 'inactive', // Inactivo hasta implementar
            ]
        );

        // Template de Acta
        DocumentTemplate::updateOrCreate(
            ['code' => 'TPL_ACTA'],
            [
                'name' => 'Acta de Evaluación',
                'description' => 'Template para actas de evaluación',
                'category' => 'acta',
                'content' => '<h1>Template de Acta</h1><p>Por implementar</p>',
                'variables' => [],
                'signature_required' => true,
                'status' => 'inactive',
            ]
        );

        $this->command->info('Templates de documentos creados exitosamente.');
    }
}
