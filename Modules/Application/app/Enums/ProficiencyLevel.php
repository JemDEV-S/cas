<?php

namespace Modules\Application\Enums;

enum ProficiencyLevel: string
{
    case BASIC = 'BASICO';
    case INTERMEDIATE = 'INTERMEDIO';
    case ADVANCED = 'AVANZADO';
    case EXPERT = 'EXPERTO';

    public function label(): string
    {
        return match($this) {
            self::BASIC => 'Básico',
            self::INTERMEDIATE => 'Intermedio',
            self::ADVANCED => 'Avanzado',
            self::EXPERT => 'Experto',
        };
    }

    public function score(): int
    {
        return match($this) {
            self::BASIC => 1,
            self::INTERMEDIATE => 2,
            self::ADVANCED => 3,
            self::EXPERT => 4,
        };
    }
}
