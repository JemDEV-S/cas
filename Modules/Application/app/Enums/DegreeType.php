<?php

namespace Modules\Application\Enums;

enum DegreeType: string
{
    case HIGH_SCHOOL = 'SECUNDARIA';
    case TECHNICAL = 'TECNICO';
    case BACHELOR = 'BACHILLER';
    case PROFESSIONAL_TITLE = 'TITULO';
    case MASTER = 'MAESTRIA';
    case DOCTORATE = 'DOCTORADO';

    public function label(): string
    {
        return match($this) {
            self::HIGH_SCHOOL => 'Educación Secundaria',
            self::TECHNICAL => 'Técnico',
            self::BACHELOR => 'Bachiller',
            self::PROFESSIONAL_TITLE => 'Título Profesional',
            self::MASTER => 'Maestría',
            self::DOCTORATE => 'Doctorado',
        };
    }

    public function level(): int
    {
        return match($this) {
            self::HIGH_SCHOOL => 1,
            self::TECHNICAL => 2,
            self::BACHELOR => 3,
            self::PROFESSIONAL_TITLE => 4,
            self::MASTER => 5,
            self::DOCTORATE => 6,
        };
    }

    public static function fromLevel(int $level): ?self
    {
        return match($level) {
            1 => self::HIGH_SCHOOL,
            2 => self::TECHNICAL,
            3 => self::BACHELOR,
            4 => self::PROFESSIONAL_TITLE,
            5 => self::MASTER,
            6 => self::DOCTORATE,
            default => null,
        };
    }
}
