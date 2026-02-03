<?php

namespace Modules\Document\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobProfile\Entities\JobProfile;

class WinnerAssignmentReportService
{
    const MIN_FINAL_SCORE = 70;
    const DEFAULT_ACCESITARIOS = 2;

    /**
     * Obtener postulaciones con resultados finales asignados
     */
    public function getAssignedApplications(string $jobPostingId): Collection
    {
        // Obtener IDs de perfiles que pertenecen a esta convocatoria
        $profileIds = JobProfile::where('job_posting_id', $jobPostingId)->pluck('id');

        if ($profileIds->isEmpty()) {
            return collect();
        }

        // Obtener postulaciones con resultado de selección asignado
        $applications = Application::query()
            ->whereIn('job_profile_id', $profileIds)
            ->whereNotNull('selection_result')
            ->whereNotNull('final_score')
            ->with([
                'jobProfile.organizationalUnit',
                'jobProfile.requestingUnit',
                'jobProfile.positionCode',
                'jobProfile.vacancies',
                'specialConditions',
            ])
            ->orderBy('final_ranking')
            ->get();

        return $applications;
    }

    /**
     * Organizar postulaciones por Unidad Organizacional > Perfil > Postulaciones
     * Incluye perfiles desiertos (sin ganadores)
     */
    public function organizeByStructure(Collection $applications, string $jobPostingId = null): array
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
                        'winners' => 0,
                        'accesitarios' => 0,
                        'not_selected' => 0,
                        'vacancies' => 0,
                    ],
                ];
            }

            $profileId = $jobProfile->id;

            // Inicializar perfil si no existe
            if (!isset($structure[$unitId]['profiles'][$profileId])) {
                $vacancyCount = $jobProfile->vacancies?->count() ?? $jobProfile->total_vacancies ?? 1;
                $structure[$unitId]['profiles'][$profileId] = [
                    'id' => $profileId,
                    'code' => $jobProfile->code,
                    'title' => $jobProfile->title,
                    'position_code' => $jobProfile->positionCode?->code ?? 'N/A',
                    'position_name' => $jobProfile->positionCode?->name ?? $jobProfile->profile_name,
                    'vacancies' => $vacancyCount,
                    'applications' => [],
                    'stats' => [
                        'total' => 0,
                        'winners' => 0,
                        'accesitarios' => 0,
                        'not_selected' => 0,
                    ],
                ];
                $structure[$unitId]['stats']['vacancies'] += $vacancyCount;
            }

            // Agregar postulación
            $structure[$unitId]['profiles'][$profileId]['applications'][] = $application;

            // Actualizar estadísticas del perfil
            $structure[$unitId]['profiles'][$profileId]['stats']['total']++;
            $structure[$unitId]['stats']['total']++;

            switch ($application->selection_result) {
                case 'GANADOR':
                    $structure[$unitId]['profiles'][$profileId]['stats']['winners']++;
                    $structure[$unitId]['stats']['winners']++;
                    break;
                case 'ACCESITARIO':
                    $structure[$unitId]['profiles'][$profileId]['stats']['accesitarios']++;
                    $structure[$unitId]['stats']['accesitarios']++;
                    break;
                case 'NO_SELECCIONADO':
                    $structure[$unitId]['profiles'][$profileId]['stats']['not_selected']++;
                    $structure[$unitId]['stats']['not_selected']++;
                    break;
            }
        }

        // PASO 2: Agregar perfiles desiertos (activos pero sin ganadores)
        if ($jobPostingId) {
            $this->addDesertProfiles($structure, $jobPostingId, $applications);
        }

        // Ordenar postulaciones por ranking dentro de cada perfil
        foreach ($structure as &$unit) {
            foreach ($unit['profiles'] as &$profile) {
                usort($profile['applications'], function ($a, $b) {
                    return ($a->final_ranking ?? 999) <=> ($b->final_ranking ?? 999);
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
     * Agregar perfiles desiertos a la estructura
     * Un perfil es desierto si:
     * 1. No tiene ninguna postulación, O
     * 2. Todas sus postulaciones son NO_ELIGIBLE (no llegaron a la fase final), O
     * 3. No tiene ningún ganador asignado
     */
    private function addDesertProfiles(array &$structure, string $jobPostingId, Collection $applications): void
    {
        // Obtener IDs de perfiles que tienen al menos un GANADOR asignado
        $profilesWithWinners = $applications
            ->filter(function($app) {
                return $app->selection_result === 'GANADOR';
            })
            ->pluck('job_profile_id')
            ->unique()
            ->toArray();

        // Obtener todos los perfiles activos de la convocatoria
        $allProfiles = JobProfile::where('job_posting_id', $jobPostingId)
            ->where('status', \Modules\JobProfile\Enums\JobProfileStatusEnum::ACTIVE->value)
            ->with(['organizationalUnit', 'requestingUnit', 'positionCode', 'vacancies'])
            ->get();

        \Illuminate\Support\Facades\Log::info('Búsqueda de perfiles desiertos en resultados finales', [
            'total_profiles' => $allProfiles->count(),
            'profiles_with_winners' => count($profilesWithWinners),
            'profile_ids_with_winners' => $profilesWithWinners,
            'all_profile_ids' => $allProfiles->pluck('id')->toArray(),
        ]);

        // Identificar perfiles desiertos
        foreach ($allProfiles as $jobProfile) {
            // Si tiene al menos un GANADOR, NO es desierto
            if (in_array($jobProfile->id, $profilesWithWinners)) {
                continue;
            }

            // Es un perfil desierto
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
                        'winners' => 0,
                        'accesitarios' => 0,
                        'not_selected' => 0,
                        'vacancies' => 0,
                    ],
                ];
            }

            // Agregar perfil desierto
            $vacancyCount = $jobProfile->vacancies?->count() ?? $jobProfile->total_vacancies ?? 1;
            $structure[$unitId]['profiles'][$jobProfile->id] = [
                'id' => $jobProfile->id,
                'code' => $jobProfile->code,
                'title' => $jobProfile->title,
                'position_code' => $jobProfile->positionCode?->code ?? 'N/A',
                'position_name' => $jobProfile->positionCode?->name ?? $jobProfile->profile_name,
                'vacancies' => $vacancyCount,
                'applications' => [],
                'is_desert' => true, // Marcador de perfil desierto
                'stats' => [
                    'total' => 0,
                    'winners' => 0,
                    'accesitarios' => 0,
                    'not_selected' => 0,
                ],
            ];
            $structure[$unitId]['stats']['vacancies'] += $vacancyCount;
        }
    }

    /**
     * Calcular estadísticas globales
     */
    public function calculateGlobalStats(array $organizedData): array
    {
        $stats = [
            'total' => 0,
            'winners' => 0,
            'accesitarios' => 0,
            'not_selected' => 0,
            'vacancies' => 0,
            'units_count' => count($organizedData),
            'profiles_count' => 0,
        ];

        foreach ($organizedData as $unit) {
            $stats['total'] += $unit['stats']['total'];
            $stats['winners'] += $unit['stats']['winners'];
            $stats['accesitarios'] += $unit['stats']['accesitarios'];
            $stats['not_selected'] += $unit['stats']['not_selected'];
            $stats['vacancies'] += $unit['stats']['vacancies'];
            $stats['profiles_count'] += count($unit['profiles']);
        }

        return $stats;
    }

    /**
     * Generar reporte completo
     */
    public function generateReport(JobPosting $jobPosting): array
    {
        // Obtener postulaciones con asignación
        $applications = $this->getAssignedApplications($jobPosting->id);

        // Organizar por estructura (incluye perfiles desiertos)
        $organizedData = $this->organizeByStructure($applications, $jobPosting->id);

        // Calcular estadísticas globales
        $globalStats = $this->calculateGlobalStats($organizedData);

        return [
            'posting' => $jobPosting,
            'title' => 'CUADRO DE MERITOS - RESULTADOS FINALES',
            'subtitle' => $jobPosting->title ?? 'Proceso de Seleccion CAS ' . $jobPosting->year,
            'phase' => 'Resultados Finales - Asignacion de Ganadores',
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
            'min_score' => self::MIN_FINAL_SCORE,
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
        $html = View::make('document::templates.result_winner_assignment_mdsj', $reportData)->render();

        // Generar PDF con DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'Arial');

        // Generar nombre de archivo
        $fileName = sprintf(
            'Cuadro_Meritos_%s_%s.pdf',
            str_replace(['/', '\\', ' '], '_', $jobPosting->code),
            now()->format('Y-m-d_His')
        );

        $directory = $outputPath ?? storage_path('app/reports/winner-assignment');
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
        $html = View::make('document::templates.result_winner_assignment_mdsj', $reportData)->render();

        // Generar PDF con DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'Arial');

        // Generar nombre de archivo
        $fileName = sprintf(
            'Cuadro_Meritos_%s_%s.pdf',
            str_replace(['/', '\\', ' '], '_', $jobPosting->code),
            now()->format('Y-m-d_His')
        );

        // Retornar como descarga
        return $pdf->download($fileName);
    }
}
