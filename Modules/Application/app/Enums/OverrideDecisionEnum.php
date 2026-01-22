<?php

namespace Modules\Application\Enums;

enum OverrideDecisionEnum: string
{
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match($this) {
            self::APPROVED => 'Procede',
            self::REJECTED => 'No Procede',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::APPROVED => 'green',
            self::REJECTED => 'red',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::APPROVED => 'El reclamo procede y el postulante pasa a estado APTO',
            self::REJECTED => 'El reclamo no procede y el postulante mantiene estado NO APTO',
        };
    }
}
