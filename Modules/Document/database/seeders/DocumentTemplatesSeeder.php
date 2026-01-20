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
                    'organizational_unit', 'parent_organizational_unit', 'requesting_unit',
                    'required_position', 'job_level', 'contract_type', 'salary_range',
                    'work_regime', 'total_vacancies', 'mission', 'main_functions',
                    'education_level', 'career_field', 'title_required', 'colegiatura_required',
                    'general_experience_years', 'specific_experience_years',
                    'specific_experience_description', 'knowledge_areas', 'required_competencies',
                    'required_courses', 'working_conditions', 'justification',
                    'contract_duration', 'contract_start_date', 'contract_end_date',
                    'work_location', 'selection_process_name', 'requisitos_generales',
                    'formatted_salary', 'base_salary', 'position_min_experience',
                    'position_specific_experience', 'requested_by', 'reviewed_by',
                    'approved_by', 'requested_at', 'reviewed_at', 'approved_at',
                    'generation_date', 'generation_time', 'anexo2', 'published_profile',
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

        // Template de Ficha de Postulación
        DocumentTemplate::updateOrCreate(
            ['code' => 'TPL_APPLICATION_SHEET'],
            [
                'name' => 'Ficha de Postulación',
                'description' => 'Template oficial para ficha de postulación del candidato',
                'category' => 'postulacion',
                'content' => file_get_contents(__DIR__ . '/../../resources/views/templates/application_sheet.blade.php'),
                'variables' => [
                    'application_code', 'application_date', 'job_posting_title', 'job_posting_code',
                    'job_profile_name', 'profile_code', 'vacancy_code',
                    'full_name', 'dni', 'birth_date', 'age', 'email', 'phone', 'mobile_phone', 'address',
                    'academics', 'experiences', 'general_experiences', 'specific_experiences',
                    'trainings', 'knowledge', 'professional_registrations', 'special_conditions',
                    'ip_address', 'generation_date', 'generation_time',
                ],
                'signature_required' => false,
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'margins' => [
                    'top' => 20,
                    'right' => 15,
                    'bottom' => 20,
                    'left' => 15,
                ],
                'status' => 'active',
            ]
        );

        // Template de Resultado de Elegibilidad MDSJ
        DocumentTemplate::updateOrCreate(
            ['code' => 'TPL_RESULT_ELIGIBILITY_MDSJ'],
            [
                'name' => 'Resultado de Elegibilidad - MDSJ',
                'description' => 'Template oficial para resultados de elegibilidad organizado por Unidad Organizacional y Perfil - Municipalidad Distrital de San Jerónimo',
                'category' => 'resultado',
                'content' => file_get_contents(__DIR__ . '/../../resources/views/templates/result_eligibility_mdsj.blade.php'),
                'variables' => [
                    'title', 'subtitle', 'posting', 'phase', 'date', 'time',
                    'stats', 'units', 'signers', 'document_code', 'generated_at',
                ],
                'signature_required' => true,
                'signature_workflow_type' => 'sequential',
                'signers_config' => [
                    [
                        'role_key' => 'presidente_comite',
                        'type' => 'aprobacion',
                        'role' => 'Presidente del Comité',
                    ],
                    [
                        'role_key' => 'miembro_titular_1',
                        'type' => 'visto_bueno',
                        'role' => 'Miembro Titular',
                    ],
                    [
                        'role_key' => 'miembro_titular_2',
                        'type' => 'visto_bueno',
                        'role' => 'Miembro Titular',
                    ],
                ],
                'signature_positions' => [
                    [
                        'page' => -1,
                        'x' => 30,
                        'y' => 50,
                        'width' => 150,
                        'height' => 60,
                    ],
                    [
                        'page' => -1,
                        'x' => 220,
                        'y' => 50,
                        'width' => 150,
                        'height' => 60,
                    ],
                    [
                        'page' => -1,
                        'x' => 410,
                        'y' => 50,
                        'width' => 150,
                        'height' => 60,
                    ],
                ],
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'margins' => [
                    'top' => 12,
                    'right' => 10,
                    'bottom' => 15,
                    'left' => 10,
                ],
                'status' => 'active',
            ]
        );

        $this->command->info('Templates de documentos creados exitosamente.');
    }
}
