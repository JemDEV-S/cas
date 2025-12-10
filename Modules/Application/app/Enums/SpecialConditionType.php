<?php

namespace Modules\Application\Enums;

enum SpecialConditionType: string
{
    case DISABILITY = 'DISABILITY';
    case MILITARY = 'MILITARY';
    case ATHLETE_NATIONAL = 'ATHLETE_NATIONAL';
    case ATHLETE_INTL = 'ATHLETE_INTL';
    case TERRORISM = 'TERRORISM';

    public function label(): string
    {
        return match($this) {
            self::DISABILITY => 'Persona con Discapacidad',
            self::MILITARY => 'Licenciado de las FF.AA.',
            self::ATHLETE_NATIONAL => 'Deportista Calificado Nacional',
            self::ATHLETE_INTL => 'Deportista Calificado Internacional',
            self::TERRORISM => 'Víctima del Terrorismo',
        };
    }

    public function bonusPercentage(): float
    {
        return match($this) {
            self::DISABILITY => 15.0,
            self::MILITARY => 10.0,
            self::ATHLETE_NATIONAL => 10.0,
            self::ATHLETE_INTL => 15.0,
            self::TERRORISM => 10.0,
        };
    }

    public function description(): string
    {
        return match($this) {
            self::DISABILITY => 'Bonificación del 15% según Ley 29973',
            self::MILITARY => 'Bonificación del 10% según Ley 29248',
            self::ATHLETE_NATIONAL => 'Bonificación del 10% según Ley 27674',
            self::ATHLETE_INTL => 'Bonificación del 15% según Ley 27674',
            self::TERRORISM => 'Bonificación del 10% según Ley 27277',
        };
    }
}
