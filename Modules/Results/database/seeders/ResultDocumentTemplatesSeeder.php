<?php

namespace Modules\Results\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Document\Entities\DocumentTemplate;

class ResultDocumentTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Leer contenido de los templates Blade
        $templateEligibility = file_get_contents(
            module_path('Document', 'resources/views/templates/result_eligibility.blade.php')
        );
        $templateCurriculum = file_get_contents(
            module_path('Document', 'resources/views/templates/result_curriculum.blade.php')
        );
        $templateFinal = file_get_contents(
            module_path('Document', 'resources/views/templates/result_final.blade.php')
        );

        $templates = [
            [
                'code' => 'RESULT_ELIGIBILITY',
                'name' => 'Acta de Resultados - Evaluación de Requisitos Mínimos',
                'description' => 'Template para publicar resultados de elegibilidad (APTO/NO APTO) de la Fase 4',
                'category' => 'result',
                'content' => $templateEligibility,
                'variables' => [
                    'posting', 'title', 'subtitle', 'applications', 'aptos', 'no_aptos', 'stats', 'date', 'phase'
                ],
                'signature_required' => true,
                'signature_workflow_type' => 'sequential',
                'signers_config' => [
                    ['role' => 'Presidente del Jurado', 'required' => true],
                    ['role' => 'Jurado Titular', 'required' => true],
                ],
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'status' => 'active',
                'metadata' => [
                    'phase' => 'PHASE_04',
                    'view_name' => 'result_eligibility',
                ],
            ],
            [
                'code' => 'RESULT_CURRICULUM',
                'name' => 'Acta de Resultados - Evaluación Curricular',
                'description' => 'Template para publicar resultados de evaluación curricular con ranking de la Fase 7',
                'category' => 'result',
                'content' => $templateCurriculum,
                'variables' => [
                    'posting', 'title', 'subtitle', 'applications', 'date', 'phase'
                ],
                'signature_required' => true,
                'signature_workflow_type' => 'sequential',
                'signers_config' => [
                    ['role' => 'Presidente del Jurado', 'required' => true],
                    ['role' => 'Jurado Titular 1', 'required' => true],
                    ['role' => 'Jurado Titular 2', 'required' => true],
                ],
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'status' => 'active',
                'metadata' => [
                    'phase' => 'PHASE_07',
                    'view_name' => 'result_curriculum',
                ],
            ],
            [
                'code' => 'RESULT_FINAL',
                'name' => 'Acta de Resultados Finales',
                'description' => 'Template para publicar resultados finales post-entrevista de la Fase 9',
                'category' => 'result',
                'content' => $templateFinal,
                'variables' => [
                    'posting', 'title', 'subtitle', 'applications', 'date', 'phase'
                ],
                'signature_required' => true,
                'signature_workflow_type' => 'sequential',
                'signers_config' => [
                    ['role' => 'Presidente del Jurado', 'required' => true],
                    ['role' => 'Jurado Titular 1', 'required' => true],
                    ['role' => 'Jurado Titular 2', 'required' => true],
                ],
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'status' => 'active',
                'metadata' => [
                    'phase' => 'PHASE_09',
                    'view_name' => 'result_final',
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            DocumentTemplate::updateOrCreate(
                ['code' => $templateData['code']],
                $templateData
            );
        }

        $this->command->info('Templates de resultados creados/actualizados exitosamente');
    }
}
