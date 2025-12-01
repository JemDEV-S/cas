<?php

namespace Modules\JobPosting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\JobPosting\Entities\ProcessPhase;

class ProcessPhasesSeeder extends Seeder
{
    public function run(): void
    {
        $phases = [
            [
                'code' => 'PHASE_01_APPROVAL',
                'phase_number' => 1,
                'name' => 'Aprobación de la Convocatoria',
                'description' => 'Aprobación interna de bases y perfiles',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_02_PUBLICATION',
                'phase_number' => 2,
                'name' => 'Publicación de la Convocatoria',
                'description' => 'Publicación en el portal de talento Perú y portal institucional',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_03_REGISTRATION',
                'phase_number' => 3,
                'name' => 'Registro Virtual de Postulantes',
                'description' => 'Recepción de fichas de postulación',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_04_ELIGIBLE_PUB',
                'phase_number' => 4,
                'name' => 'Publicación de postulantes APTOS',
                'description' => 'Filtro inicial de postulantes',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_05_CV_SUBMISSION',
                'phase_number' => 5,
                'name' => 'Presentación de CV documentado',
                'description' => 'Entrega física o digital de documentos sustentatorios',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_06_CV_EVALUATION',
                'phase_number' => 6,
                'name' => 'Evaluación Curricular',
                'description' => 'Calificación de la hoja de vida por el comité',
                'requires_evaluation' => true, // IMPORTANTE: Esta fase lleva puntaje
            ],
            [
                'code' => 'PHASE_07_CV_RESULTS',
                'phase_number' => 7,
                'name' => 'Publicación de resultados curriculares',
                'description' => 'Publicación de puntajes de CV',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_08_INTERVIEW',
                'phase_number' => 8,
                'name' => 'Entrevista Personal',
                'description' => 'Entrevista presencial o virtual con el comité',
                'requires_evaluation' => true, // IMPORTANTE: Esta fase lleva puntaje
            ],
            [
                'code' => 'PHASE_09_FINAL_RESULTS',
                'phase_number' => 9,
                'name' => 'Publicación de resultados finales',
                'description' => 'Publicación del cuadro de méritos final',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_10_CONTRACT',
                'phase_number' => 10,
                'name' => 'Suscripción de contrato',
                'description' => 'Firma del contrato administrativo de servicios',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_11_INDUCTION',
                'phase_number' => 11,
                'name' => 'Charla de Inducción',
                'description' => 'Inducción al puesto y a la entidad',
                'requires_evaluation' => false,
            ],
            [
                'code' => 'PHASE_12_START',
                'phase_number' => 12,
                'name' => 'Inicio de labores',
                'description' => 'Primer día de trabajo',
                'requires_evaluation' => false,
            ],
        ];

        foreach ($phases as $phase) {
            ProcessPhase::updateOrCreate(
                ['code' => $phase['code']], // Buscamos por código para no duplicar
                [
                    'name' => $phase['name'],
                    'description' => $phase['description'],
                    'phase_number' => $phase['phase_number'],
                    'requires_evaluation' => $phase['requires_evaluation'],
                    'is_active' => true,
                    'is_system' => true, // Protegido del sistema
                ]
            );
        }
    }
}