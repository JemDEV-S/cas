<?php

namespace Modules\Document\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Application\Entities\Application;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobProfile\Entities\JobProfile;

class WinnerAdjudicationCertificateService
{
    /**
     * Obtener solo postulaciones ganadoras y accesitarias
     */
    public function getWinnersAndAccesitarios(string $jobPostingId): Collection
    {
        // Obtener IDs de perfiles que pertenecen a esta convocatoria
        $profileIds = JobProfile::where('job_posting_id', $jobPostingId)->pluck('id');

        if ($profileIds->isEmpty()) {
            return collect();
        }

        // Obtener solo GANADORES y ACCESITARIOS
        $applications = Application::query()
            ->whereIn('job_profile_id', $profileIds)
            ->whereIn('selection_result', ['GANADOR', 'ACCESITARIO'])
            ->with([
                'jobProfile.organizationalUnit',
                'jobProfile.requestingUnit',
                'jobProfile.positionCode',
                'jobProfile.vacancies',
            ])
            ->orderBy('job_profile_id')
            ->orderBy('selection_result') // GANADOR primero
            ->orderBy('final_ranking')
            ->get();

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
                        'winners' => 0,
                        'accesitarios' => 0,
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
                        'winners' => 0,
                        'accesitarios' => 0,
                    ],
                ];
            }

            // Agregar postulación
            $structure[$unitId]['profiles'][$profileId]['applications'][] = $application;

            // Actualizar estadísticas
            if ($application->selection_result === 'GANADOR') {
                $structure[$unitId]['profiles'][$profileId]['stats']['winners']++;
                $structure[$unitId]['stats']['winners']++;
            } elseif ($application->selection_result === 'ACCESITARIO') {
                $structure[$unitId]['profiles'][$profileId]['stats']['accesitarios']++;
                $structure[$unitId]['stats']['accesitarios']++;
            }
        }

        // Ordenar postulaciones por resultado y ranking dentro de cada perfil
        foreach ($structure as &$unit) {
            foreach ($unit['profiles'] as &$profile) {
                usort($profile['applications'], function ($a, $b) {
                    // GANADOR primero, luego ACCESITARIO
                    if ($a->selection_result !== $b->selection_result) {
                        return $a->selection_result === 'GANADOR' ? -1 : 1;
                    }
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
     * Calcular estadísticas globales
     */
    public function calculateGlobalStats(array $organizedData): array
    {
        $stats = [
            'winners' => 0,
            'accesitarios' => 0,
            'total' => 0,
            'units_count' => count($organizedData),
            'profiles_count' => 0,
        ];

        foreach ($organizedData as $unit) {
            $stats['winners'] += $unit['stats']['winners'];
            $stats['accesitarios'] += $unit['stats']['accesitarios'];
            $stats['profiles_count'] += count($unit['profiles']);
        }

        $stats['total'] = $stats['winners'] + $stats['accesitarios'];

        return $stats;
    }

    /**
     * Generar reporte completo
     */
    public function generateReport(JobPosting $jobPosting): array
    {
        // Obtener solo ganadores y accesitarios
        $applications = $this->getWinnersAndAccesitarios($jobPosting->id);

        // Organizar por estructura
        $organizedData = $this->organizeByStructure($applications);

        // Calcular estadísticas globales
        $globalStats = $this->calculateGlobalStats($organizedData);

        return [
            'posting' => $jobPosting,
            'title' => 'CONSTANCIA DE ADJUDICACIÓN',
            'subtitle' => $jobPosting->title ?? 'Proceso de Selección CAS ' . $jobPosting->year,
            'phase' => 'Resultados Finales - Ganadores y Accesitarios',
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
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
        $html = View::make('document::templates.winner_adjudication_certificate_mdsj', $reportData)->render();

        // Generar PDF con DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'Arial');

        // Generar nombre de archivo
        $fileName = sprintf(
            'Constancia_Adjudicacion_%s_%s.pdf',
            str_replace(['/', '\\', ' '], '_', $jobPosting->code),
            now()->format('Y-m-d_His')
        );

        $directory = $outputPath ?? storage_path('app/reports/adjudication-certificate');
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
        $html = View::make('document::templates.winner_adjudication_certificate_mdsj', $reportData)->render();

        // Generar PDF con DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'Arial');

        // Generar nombre de archivo
        $fileName = sprintf(
            'Constancia_Adjudicacion_%s_%s.pdf',
            str_replace(['/', '\\', ' '], '_', $jobPosting->code),
            now()->format('Y-m-d_His')
        );

        // Retornar como descarga
        return $pdf->download($fileName);
    }
}
