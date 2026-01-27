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
     */
    public function calculateAllBonuses(Application $application): array
    {
        $interviewScore = $application->interview_score ?? 0;

        // 1. Bonus por edad (< 29 anios) - sobre entrevista
        $ageBonus = $this->calculateAgeBonus($application, $interviewScore);

        // 2. Interview score con bonus joven
        $interviewScoreWithBonus = $interviewScore + $ageBonus;

        // 3. Puntaje base (CV + Entrevista con bonus)
        $baseScore = ($application->curriculum_score ?? 0) + $interviewScoreWithBonus;

        // 4. Bonus por experiencia sector publico
        $publicSectorYears = $this->calculatePublicSectorYears($application);
        $publicSectorBonus = min($publicSectorYears, self::MAX_PUBLIC_SECTOR_BONUS);

        // 5. Bonificaciones especiales (discapacidad, militar, etc) - sobre puntaje base
        $specialBonuses = $this->calculateSpecialBonuses($application, $baseScore);

        // 6. Puntaje final
        $finalScore = $baseScore + $specialBonuses['total'] + $publicSectorBonus;

        return [
            'interview_score_raw' => $interviewScore,
            'age_bonus' => round($ageBonus, 2),
            'interview_score_with_bonus' => round($interviewScoreWithBonus, 2),
            'curriculum_score' => $application->curriculum_score ?? 0,
            'base_score' => round($baseScore, 2),
            'public_sector_years' => $publicSectorYears,
            'public_sector_bonus' => round($publicSectorBonus, 2),
            'special_bonuses' => $specialBonuses,
            'special_bonus_total' => round($specialBonuses['total'], 2),
            'final_score' => round($finalScore, 2),
            'is_approved' => $finalScore >= 70,
        ];
    }

    /**
     * Calcular bonus por edad (Ley 31533, Art. 3.1)
     * 10% sobre puntaje de entrevista para menores de 29 anios
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
     * Calcular anios de experiencia en sector publico
     */
    public function calculatePublicSectorYears(Application $application): int
    {
        $totalDays = $application->experiences()
            ->where('is_public_sector', true)
            ->where('is_verified', true)
            ->sum('duration_days');

        // Convertir dias a anios (365 dias = 1 anio)
        return (int) floor($totalDays / 365);
    }

    /**
     * Calcular bonificaciones especiales (sobre puntaje base)
     */
    public function calculateSpecialBonuses(Application $application, float $baseScore): array
    {
        $bonuses = [
            'disability' => 0,      // Discapacidad 15%
            'military' => 0,        // Licenciado FF.AA. 10%
            'athlete_national' => 0, // Deportista nacional 10%
            'athlete_intl' => 0,    // Deportista internacional 15%
            'terrorism' => 0,       // Victima terrorismo 10%
            'total' => 0,
            'details' => [],
        ];

        foreach ($application->specialConditions as $condition) {
            if (!$condition->is_verified || !$condition->isValid()) {
                continue;
            }

            $percentage = $condition->bonus_percentage / 100;
            $bonus = $baseScore * $percentage;

            switch ($condition->condition_type) {
                case 'DISABILITY':
                    $bonuses['disability'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Discapacidad',
                        'law' => 'Ley 29973',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'MILITARY':
                    $bonuses['military'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Licenciado FF.AA.',
                        'law' => 'Ley 29248',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'ATHLETE_NATIONAL':
                    $bonuses['athlete_national'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Deportista Nacional',
                        'law' => 'Ley 27674',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'ATHLETE_INTL':
                    $bonuses['athlete_intl'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Deportista Internacional',
                        'law' => 'Ley 27674',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
                case 'TERRORISM':
                    $bonuses['terrorism'] = $bonus;
                    $bonuses['details'][] = [
                        'type' => 'Victima Terrorismo',
                        'law' => 'Ley 27277',
                        'percentage' => $condition->bonus_percentage,
                        'amount' => round($bonus, 2),
                    ];
                    break;
            }
        }

        // Sumar todas las bonificaciones (son acumulables)
        $bonuses['total'] = $bonuses['disability'] + $bonuses['military'] +
                           $bonuses['athlete_national'] + $bonuses['athlete_intl'] +
                           $bonuses['terrorism'];

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
