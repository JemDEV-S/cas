<?php

namespace Modules\Document\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Application\Entities\Application;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobProfile\Entities\JobProfile;
use Modules\Evaluation\Entities\Evaluation;
use Modules\Evaluation\Enums\EvaluationStatusEnum;

class CvEvaluationReportService
{
    const MIN_PASSING_SCORE = 35;
    const MAX_SCORE = 50;

    public function __construct()
    {
        //
    }

    /**
     * Obtener postulaciones con evaluaciones de CV completadas
     */
    public function getEvaluatedApplications(string $jobPostingId): Collection
    {
        // Obtener IDs de perfiles que pertenecen a esta convocatoria
        $profileIds = JobProfile::where('job_posting_id', $jobPostingId)->pluck('id');

        if ($profileIds->isEmpty()) {
            return collect();
        }

        // Obtener la fase de evaluación curricular (Fase 6)
        $cvPhase = \Modules\JobPosting\Entities\ProcessPhase::where('code', 'PHASE_06_CV_EVALUATION')
            ->first();

        if (!$cvPhase) {
            return collect();
        }

        // Subconsulta para obtener la postulación más reciente por DNI
        $latestApplications = DB::table('applications')
            ->select('dni', DB::raw('MAX(created_at) as max_created_at'))
            ->whereIn('job_profile_id', $profileIds)
            ->whereNotNull('curriculum_score')
            ->whereNull('deleted_at')
            ->groupBy('dni');

        // Obtener las postulaciones más recientes con todas sus relaciones
        $applications = Application::query()
            ->joinSub($latestApplications, 'latest', function ($join) {
                $join->on('applications.dni', '=', 'latest.dni')
                    ->on('applications.created_at', '=', 'latest.max_created_at');
            })
            ->whereIn('applications.job_profile_id', $profileIds)
            ->whereNotNull('applications.curriculum_score')
            ->with([
                'jobProfile.organizationalUnit',
                'jobProfile.requestingUnit',
                'jobProfile.positionCode',
            ])
            ->get();

        // Cargar evaluaciones de CV y evaluadores
        $applications->load(['evaluations' => function ($query) use ($cvPhase) {
            $query->where('phase_id', $cvPhase->id)
                  ->whereIn('status', [
                      EvaluationStatusEnum::SUBMITTED,
                      EvaluationStatusEnum::MODIFIED,
                  ])
                  ->with('evaluator');
        }]);

        // Agregar información del evaluador y comentarios a cada application
        foreach ($applications as $application) {
            $cvEvaluation = $application->evaluations->first();

            $application->evaluator_name = $cvEvaluation?->evaluator?->name ?? 'N/A';
            $application->evaluation_comments = $cvEvaluation?->general_comments ?? '';
        }

        return $applications;
    }

    /**
     * Organizar postulaciones por Unidad Organizacional > Perfil > Postulaciones
     */
    public function organizeByStructure(Collection $applications): array
    {
        $structure = [];

        foreach ($applications as $application) {
            $jobProfile = $application->jobProfile;
            if (!$jobProfile) {
                continue;
            }

            // Usar requesting_unit o organizational_unit
            $unit = $jobProfile->requestingUnit ?? $jobProfile->organizationalUnit;
            $unitId = $unit?->id ?? 'sin_unidad';
            $unitName = $unit?->name ?? 'Sin Unidad Asignada';
            $unitCode = $unit?->code ?? 'N/A';

            // Inicializar unidad si no existe
            if (!isset($structure[$unitId])) {
                $structure[$unitId] = [
                    'id' => $unitId,
                    'code' => $unitCode,
                    'name' => $unitName,
                    'order' => $unit?->order ?? 999,
                    'profiles' => [],
                    'stats' => [
                        'total' => 0,
                        'pass' => 0,
                        'fail' => 0,
                        'total_score' => 0,
                        'avg_score' => 0,
                    ],
                ];
            }

            $profileId = $jobProfile->id;

            // Inicializar perfil si no existe
            if (!isset($structure[$unitId]['profiles'][$profileId])) {
                $structure[$unitId]['profiles'][$profileId] = [
                    'id' => $profileId,
                    'code' => $jobProfile->code,
                    'title' => $jobProfile->title,
                    'position_code' => $jobProfile->positionCode?->code ?? 'N/A',
                    'position_name' => $jobProfile->positionCode?->name ?? $jobProfile->profile_name,
                    'vacancies' => $jobProfile->total_vacancies ?? 1,
                    'applications' => [],
                    'stats' => [
                        'total' => 0,
                        'pass' => 0,
                        'fail' => 0,
                        'total_score' => 0,
                        'avg_score' => 0,
                    ],
                ];
            }

            // Agregar postulación
            $structure[$unitId]['profiles'][$profileId]['applications'][] = $application;

            $score = $application->curriculum_score ?? 0;
            $isPassing = $score >= self::MIN_PASSING_SCORE;

            // Actualizar estadísticas del perfil
            $structure[$unitId]['profiles'][$profileId]['stats']['total']++;
            $structure[$unitId]['profiles'][$profileId]['stats']['total_score'] += $score;
            if ($isPassing) {
                $structure[$unitId]['profiles'][$profileId]['stats']['pass']++;
            } else {
                $structure[$unitId]['profiles'][$profileId]['stats']['fail']++;
            }

            // Actualizar estadísticas de la unidad
            $structure[$unitId]['stats']['total']++;
            $structure[$unitId]['stats']['total_score'] += $score;
            if ($isPassing) {
                $structure[$unitId]['stats']['pass']++;
            } else {
                $structure[$unitId]['stats']['fail']++;
            }
        }

        // Calcular promedios y ordenar postulaciones
        foreach ($structure as &$unit) {
            if ($unit['stats']['total'] > 0) {
                $unit['stats']['avg_score'] = $unit['stats']['total_score'] / $unit['stats']['total'];
            }

            foreach ($unit['profiles'] as &$profile) {
                if ($profile['stats']['total'] > 0) {
                    $profile['stats']['avg_score'] = $profile['stats']['total_score'] / $profile['stats']['total'];
                }

                // Ordenar postulaciones por puntaje descendente
                usort($profile['applications'], function ($a, $b) {
                    $scoreCompare = ($b->curriculum_score ?? 0) <=> ($a->curriculum_score ?? 0);
                    if ($scoreCompare !== 0) {
                        return $scoreCompare;
                    }
                    // Si tienen el mismo puntaje, ordenar alfabéticamente
                    return strcmp($a->full_name, $b->full_name);
                });
            }

            // Convertir profiles de array asociativo a indexado
            $unit['profiles'] = array_values($unit['profiles']);
        }

        // Ordenar unidades por orden y nombre
        uasort($structure, function ($a, $b) {
            if ($a['order'] !== $b['order']) {
                return $a['order'] <=> $b['order'];
            }
            return strcmp($a['name'], $b['name']);
        });

        return array_values($structure);
    }

    /**
     * Calcular estadísticas globales
     */
    public function calculateGlobalStats(array $organizedData): array
    {
        $stats = [
            'total' => 0,
            'pass' => 0,
            'fail' => 0,
            'total_score' => 0,
            'avg_score' => 0,
            'units_count' => count($organizedData),
            'profiles_count' => 0,
        ];

        foreach ($organizedData as $unit) {
            $stats['total'] += $unit['stats']['total'];
            $stats['pass'] += $unit['stats']['pass'];
            $stats['fail'] += $unit['stats']['fail'];
            $stats['total_score'] += $unit['stats']['total_score'];
            $stats['profiles_count'] += count($unit['profiles']);
        }

        if ($stats['total'] > 0) {
            $stats['avg_score'] = $stats['total_score'] / $stats['total'];
        }

        return $stats;
    }

    /**
     * Generar reporte completo
     */
    public function generateReport(JobPosting $jobPosting): array
    {
        // Obtener postulaciones evaluadas
        $applications = $this->getEvaluatedApplications($jobPosting->id);

        // Organizar por estructura
        $organizedData = $this->organizeByStructure($applications);

        // Calcular estadísticas globales
        $globalStats = $this->calculateGlobalStats($organizedData);

        return [
            'posting' => $jobPosting,
            'title' => 'RESULTADO DE EVALUACION CURRICULAR',
            'subtitle' => $jobPosting->title ?? 'Proceso de Seleccion CAS ' . $jobPosting->year,
            'phase' => 'Evaluacion Curricular - Fase 6',
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
            'min_score' => self::MIN_PASSING_SCORE,
            'max_score' => self::MAX_SCORE,
            'stats' => $globalStats,
            'units' => $organizedData,
            'generated_at' => now(),
        ];
    }

    /**
     * Generar PDF del reporte
     */
    public function generatePdf(JobPosting $jobPosting, ?string $outputPath = null): string
    {
        $reportData = $this->generateReport($jobPosting);

        // Renderizar el template blade directamente
        $html = View::make('document::templates.result_cv_evaluation_mdsj', $reportData)->render();

        // Generar PDF con DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'Arial');

        // Generar nombre de archivo
        $fileName = sprintf(
            'Resultado_Evaluacion_CV_%s_%s.pdf',
            str_replace(['/', '\\', ' '], '_', $jobPosting->code),
            now()->format('Y-m-d_His')
        );

        $directory = $outputPath ?? storage_path('app/reports/cv-evaluation');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;

        // Guardar PDF
        $pdf->save($filePath);

        return $filePath;
    }

    /**
     * Descargar PDF directamente al navegador
     */
    public function downloadPdf(JobPosting $jobPosting): \Symfony\Component\HttpFoundation\Response
    {
        $reportData = $this->generateReport($jobPosting);

        // Renderizar el template blade directamente
        $html = View::make('document::templates.result_cv_evaluation_mdsj', $reportData)->render();

        // Generar PDF con DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'Arial');

        // Generar nombre de archivo
        $fileName = sprintf(
            'Resultado_Evaluacion_CV_%s_%s.pdf',
            str_replace(['/', '\\', ' '], '_', $jobPosting->code),
            now()->format('Y-m-d_His')
        );

        // Retornar como descarga
        return $pdf->download($fileName);
    }
}
