<?php

namespace Modules\Application\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Servicio para calcular la elegibilidad y experiencia laboral
 *
 * Características principales:
 * - Calcula experiencia general y específica
 * - Detecta y fusiona superposiciones de fechas (overlaps)
 * - Devuelve tiempo en formato "X Años, Y Meses, Z Días"
 */
class EligibilityCalculatorService
{
    /**
     * Calcular experiencia total considerando superposiciones
     *
     * @param array $experiences Array de experiencias con start_date y end_date
     * @return array ['total_days' => int, 'formatted' => string, 'years' => int, 'months' => int, 'days' => int]
     */
    public function calculateTotalExperience(array $experiences): array
    {
        if (empty($experiences)) {
            return $this->formatExperience(0);
        }

        // Convertir a periodos de Carbon
        $periods = $this->convertToPeriods($experiences);

        // Fusionar periodos superpuestos
        $mergedPeriods = $this->mergeOverlappingPeriods($periods);

        // Calcular días totales
        $totalDays = $this->calculateTotalDays($mergedPeriods);

        return $this->formatExperience($totalDays);
    }

    /**
     * Calcular experiencia general
     */
    public function calculateGeneralExperience(array $experiences): array
    {
        $generalExperiences = array_filter($experiences, fn($exp) => !($exp['is_specific'] ?? false));
        return $this->calculateTotalExperience($generalExperiences);
    }

    /**
     * Calcular experiencia específica
     */
    public function calculateSpecificExperience(array $experiences): array
    {
        $specificExperiences = array_filter($experiences, fn($exp) => $exp['is_specific'] ?? false);
        return $this->calculateTotalExperience($specificExperiences);
    }

    /**
     * Calcular experiencia en sector público
     */
    public function calculatePublicSectorExperience(array $experiences): array
    {
        $publicExperiences = array_filter($experiences, fn($exp) => $exp['is_public_sector'] ?? false);
        return $this->calculateTotalExperience($publicExperiences);
    }

    /**
     * Detectar superposiciones entre experiencias
     *
     * @return array Lista de superposiciones detectadas con detalles
     */
    public function detectOverlaps(array $experiences): array
    {
        $overlaps = [];
        $count = count($experiences);

        for ($i = 0; $i < $count - 1; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $overlap = $this->checkOverlap($experiences[$i], $experiences[$j]);
                if ($overlap) {
                    $overlaps[] = [
                        'experience_1' => $experiences[$i],
                        'experience_2' => $experiences[$j],
                        'overlap_days' => $overlap['days'],
                        'overlap_start' => $overlap['start'],
                        'overlap_end' => $overlap['end'],
                    ];
                }
            }
        }

        return $overlaps;
    }

    /**
     * Convertir array de experiencias a periodos de Carbon
     */
    private function convertToPeriods(array $experiences): array
    {
        $periods = [];

        foreach ($experiences as $exp) {
            $start = Carbon::parse($exp['start_date']);
            $end = Carbon::parse($exp['end_date']);

            // Validar que la fecha de inicio sea anterior a la de fin
            if ($start->greaterThan($end)) {
                throw new \InvalidArgumentException(
                    "La fecha de inicio ({$start->toDateString()}) no puede ser posterior a la fecha de fin ({$end->toDateString()})"
                );
            }

            $periods[] = [
                'start' => $start,
                'end' => $end,
            ];
        }

        // Ordenar por fecha de inicio
        usort($periods, fn($a, $b) => $a['start']->timestamp <=> $b['start']->timestamp);

        return $periods;
    }

    /**
     * Fusionar periodos superpuestos
     *
     * Algoritmo:
     * 1. Ordenar por fecha de inicio
     * 2. Iterar comparando periodo actual con el siguiente
     * 3. Si hay superposición, fusionar tomando el máximo end_date
     * 4. Si no hay superposición, agregar como periodo independiente
     */
    private function mergeOverlappingPeriods(array $periods): array
    {
        if (empty($periods)) {
            return [];
        }

        $merged = [];
        $current = $periods[0];

        for ($i = 1; $i < count($periods); $i++) {
            $next = $periods[$i];

            // Verificar si hay superposición o son consecutivos
            // Se considera overlap si el siguiente empieza antes o el mismo día que termina el actual
            if ($next['start']->lessThanOrEqualTo($current['end']->addDay())) {
                // Hay superposición o son consecutivos, fusionar
                $current['end'] = $current['end']->greaterThan($next['end'])
                    ? $current['end']
                    : $next['end'];
            } else {
                // No hay superposición, guardar el periodo actual y avanzar
                $merged[] = $current;
                $current = $next;
            }
        }

        // Agregar el último periodo
        $merged[] = $current;

        return $merged;
    }

    /**
     * Calcular días totales de todos los periodos
     */
    private function calculateTotalDays(array $periods): int
    {
        $totalDays = 0;

        foreach ($periods as $period) {
            // Incluir el día final en el cálculo
            $days = $period['start']->diffInDays($period['end']) + 1;
            $totalDays += $days;
        }

        return $totalDays;
    }

    /**
     * Formatear días totales a años, meses y días
     */
    private function formatExperience(int $totalDays): array
    {
        $years = floor($totalDays / 365);
        $remainingDays = $totalDays % 365;
        $months = floor($remainingDays / 30);
        $days = $remainingDays % 30;

        $parts = [];
        if ($years > 0) {
            $parts[] = "{$years} año" . ($years > 1 ? 's' : '');
        }
        if ($months > 0) {
            $parts[] = "{$months} mes" . ($months > 1 ? 'es' : '');
        }
        if ($days > 0 || empty($parts)) {
            $parts[] = "{$days} día" . ($days > 1 ? 's' : '');
        }

        return [
            'total_days' => $totalDays,
            'years' => (int) $years,
            'months' => (int) $months,
            'days' => (int) $days,
            'formatted' => implode(', ', $parts),
            'decimal_years' => round($totalDays / 365, 2),
        ];
    }

    /**
     * Verificar si dos experiencias se superponen
     */
    private function checkOverlap(array $exp1, array $exp2): ?array
    {
        $start1 = Carbon::parse($exp1['start_date']);
        $end1 = Carbon::parse($exp1['end_date']);
        $start2 = Carbon::parse($exp2['start_date']);
        $end2 = Carbon::parse($exp2['end_date']);

        // Determinar si hay superposición
        $overlapStart = $start1->greaterThan($start2) ? $start1 : $start2;
        $overlapEnd = $end1->lessThan($end2) ? $end1 : $end2;

        if ($overlapStart->lessThanOrEqualTo($overlapEnd)) {
            return [
                'start' => $overlapStart->toDateString(),
                'end' => $overlapEnd->toDateString(),
                'days' => $overlapStart->diffInDays($overlapEnd) + 1,
            ];
        }

        return null;
    }

    /**
     * Convertir años decimales a formato "X Años, Y Meses, Z Días"
     */
    public function decimalYearsToFormatted(float $years): array
    {
        $totalDays = (int) round($years * 365);
        return $this->formatExperience($totalDays);
    }

    /**
     * Validar si cumple con el requisito de experiencia
     */
    public function meetsRequirement(array $experiences, float $requiredYears, bool $specificOnly = false): bool
    {
        if ($specificOnly) {
            $result = $this->calculateSpecificExperience($experiences);
        } else {
            $result = $this->calculateTotalExperience($experiences);
        }

        return $result['decimal_years'] >= $requiredYears;
    }
}
