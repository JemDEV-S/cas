<?php

namespace Modules\Results\Services;

use Carbon\Carbon;
use Modules\Application\Entities\Application;

class BonusCalculationService
{
    const MAX_PUBLIC_SECTOR_BONUS = 3.0;
    const AGE_LIMIT_FOR_BONUS = 29;
    const AGE_BONUS_PERCENTAGE = 0.10;

    /**
     * Calcular todas las bonificaciones para una postulacion
     *
     * NOTA: Los valores de interview_score, age_bonus y military_bonus ya deben estar
     * guardados en la BD por el InterviewResultProcessingService. Este método los lee
     * y calcula las bonificaciones restantes.
     */
    public function calculateAllBonuses(Application $application): array
    {
        $interviewScore = $application->interview_score ?? 0;
        $age = $this->getAge($application);

        // PASO 1: Bonificaciones sobre puntaje de entrevista RAW
        // Estos valores YA fueron calculados y guardados por InterviewResultProcessingService
        $ageBonus = $application->age_bonus ?? 0;
        $militaryBonus = $application->military_bonus ?? 0;
        $interviewScoreWithBonus = $application->interview_score_with_bonus ?? ($interviewScore + $ageBonus + $militaryBonus);

        // PASO 2: Puntaje base
        $baseScore = ($application->curriculum_score ?? 0) + $interviewScoreWithBonus;

        // PASO 3: Bonus por experiencia sector publico (SOLO si < 29 años - Ley 31533, Art. 3.2)
        $publicSectorYears = $this->calculatePublicSectorYears($application);
        $publicSectorBonus = ($age !== null && $age < self::AGE_LIMIT_FOR_BONUS)
            ? min($publicSectorYears, self::MAX_PUBLIC_SECTOR_BONUS)
            : 0;

        // PASO 4: Subtotal
        $subtotal = $baseScore + $publicSectorBonus;

        // PASO 5: Bonificaciones especiales sobre SUBTOTAL (discapacidad, deportistas, terrorismo)
        // NOTA: Excluimos MILITARY porque ya se aplicó sobre entrevista RAW
        $specialBonuses = $this->calculateSpecialBonuses($application, $subtotal, ['MILITARY']);

        // PASO 6: Puntaje final
        $finalScore = $subtotal + $specialBonuses['total'];

        return [
            'interview_score_raw' => $interviewScore,
            'age' => $age,
            'age_bonus' => round($ageBonus, 2),
            'military_bonus' => round($militaryBonus, 2),
            'interview_score_with_bonus' => round($interviewScoreWithBonus, 2),
            'curriculum_score' => $application->curriculum_score ?? 0,
            'base_score' => round($baseScore, 2),
            'public_sector_years' => $publicSectorYears,
            'public_sector_bonus' => round($publicSectorBonus, 2),
            'subtotal' => round($subtotal, 2),
            'special_bonuses' => $specialBonuses,
            'special_bonus_total' => round($specialBonuses['total'], 2),
            'final_score' => round($finalScore, 2),
            'is_approved' => $finalScore >= 70,
        ];
    }

    /**
     * Calcular bonus por edad (Ley 31533, Art. 3.1)
     * 10% sobre puntaje de entrevista RAW para menores de 29 años
     */
    public function calculateAgeBonus(Application $application, float $interviewScore): float
    {
        if (!$application->birth_date) {
            return 0;
        }

        $age = Carbon::parse($application->birth_date)->age;

        if ($age < self::AGE_LIMIT_FOR_BONUS) {
            return $interviewScore * self::AGE_BONUS_PERCENTAGE;
        }

        return 0;
    }

    /**
     * Calcular bonus por ser licenciado de FF.AA.
     * (RPE 61-2010-SERVIR/PE, Art. 4)
     * 10% sobre puntaje de entrevista RAW
     *
     * Requisitos:
     * - Debe estar indicado en la Hoja de Vida
     * - Debe adjuntar documento oficial de autoridad competente
     * - El documento debe acreditar la condición de licenciado
     */
    public function calculateMilitaryBonus(Application $application, float $interviewScore): float
    {
        // Si no hay specialConditions cargadas, hacer query
        if (!$application->relationLoaded('specialConditions')) {
            $hasMilitaryCondition = $application->specialConditions()
                ->where('condition_type', 'MILITARY')
                //->where('is_verified', true)
                ->whereRaw('(expiry_date IS NULL OR expiry_date >= CURDATE())')
                ->exists();
        } else {
            // Usar la colección ya cargada
            $hasMilitaryCondition = $application->specialConditions
                ->where('condition_type', 'MILITARY')
                //->where('is_verified', true)
                // ->filter(function ($condition) {
                //     return !$condition->expiry_date || $condition->expiry_date >= now()->toDateString();
                // })
                ->isNotEmpty();
        }

        if ($hasMilitaryCondition) {
            return $interviewScore * 0.10; // 10% sobre entrevista RAW
        }

        return 0;
    }

    /**
     * Calcular anios de experiencia en sector publico
     * Maneja tanto experiencias pre-cargadas como queries frescos
     */
    public function calculatePublicSectorYears(Application $application): int
    {
        // Si las experiencias ya están cargadas (con eager loading), usar la colección
        if ($application->relationLoaded('experiences')) {
            $totalDays = $application->experiences
                ->where('is_public_sector', true)
                ->where('is_verified', true)
                ->sum('duration_days');
        } else {
            // Si no están cargadas, hacer query fresco
            $totalDays = $application->experiences()
                ->where('is_public_sector', true)
                ->where('is_verified', true)
                ->sum('duration_days');
        }

        // Convertir dias a anios (365 dias = 1 anio)
        return (int) floor($totalDays / 365);
    }

    /**
     * Calcular bonificaciones especiales sobre SUBTOTAL
     * (discapacidad, deportistas, terrorismo)
     *
     * NOTA: MILITARY (FF.AA.) NO se calcula aquí porque se aplica sobre entrevista RAW,
     *       no sobre el subtotal
     *
     * @param Application $application
     * @param float $subtotal El puntaje sobre el cual calcular las bonificaciones
     * @param array $exclude Tipos de condiciones a excluir (ej: ['MILITARY'])
     */
    public function calculateSpecialBonuses(
        Application $application,
        float $subtotal,
        array $exclude = []
    ): array {
        $bonuses = [
            'disability' => 0,       // Discapacidad 15% sobre subtotal
            'athlete_national' => 0, // Deportista nacional 10% sobre subtotal
            'athlete_intl' => 0,     // Deportista internacional 15% sobre subtotal
            'terrorism' => 0,        // Victima terrorismo 10% sobre subtotal
            'total' => 0,
            'details' => [],
        ];

        // Si no hay specialConditions cargadas, retornar vacío
        if (!$application->relationLoaded('specialConditions')) {
            return $bonuses;
        }

        foreach ($application->specialConditions as $condition) {
            // Saltar si no está verificada
            // if (!$condition->is_verified) {
            //     continue;
            // }

            // Saltar si está excluida (ej: MILITARY)
            if (in_array($condition->condition_type, $exclude)) {
                continue;
            }

            // Verificar fecha de vencimiento
            // if ($condition->expiry_date && $condition->expiry_date < now()->toDateString()) {
            //     continue;
            // }

            $percentage = $condition->bonus_percentage / 100;
            $bonus = $subtotal * $percentage;

            switch ($condition->condition_type) {
                case 'DISABILITY':
                    $bonuses['disability'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Discapacidad',
                        'law' => 'Ley 29973 Art. 48',
                        'percentage' => $condition->bonus_percentage,
                        'base' => 'subtotal',
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'ATHLETE_NATIONAL':
                    $bonuses['athlete_national'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Deportista Nacional',
                        'law' => 'Ley 27674',
                        'percentage' => $condition->bonus_percentage,
                        'base' => 'subtotal',
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'ATHLETE_INTL':
                    $bonuses['athlete_intl'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Deportista Internacional',
                        'law' => 'Ley 27674',
                        'percentage' => $condition->bonus_percentage,
                        'base' => 'subtotal',
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'TERRORISM':
                    $bonuses['terrorism'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Victima Terrorismo',
                        'law' => 'Ley 27277',
                        'percentage' => $condition->bonus_percentage,
                        'base' => 'subtotal',
                        'amount' => round($bonus, 2),
                    ];
                    break;
            }
        }

        // Sumar todas las bonificaciones (son acumulables)
        $bonuses['total'] = $bonuses['disability'] + $bonuses['athlete_national'] +
                           $bonuses['athlete_intl'] + $bonuses['terrorism'];

        return $bonuses;
    }

    /**
     * Obtener edad del postulante
     */
    public function getAge(Application $application): ?int
    {
        if (!$application->birth_date) {
            return null;
        }

        return Carbon::parse($application->birth_date)->age;
    }
}
