<?php

namespace Modules\JobProfile\Enums;

enum VacancyStatusEnum: string
{
    case AVAILABLE = 'available';
    case IN_PROCESS = 'in_process';
    case FILLED = 'filled';
    case VACANT = 'vacant';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Disponible',
            self::IN_PROCESS => 'En Proceso',
            self::FILLED => 'Cubierta',
            self::VACANT => 'Desierta',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::AVAILABLE => 'Vacante disponible para postulaciones',
            self::IN_PROCESS => 'Vacante en proceso de selección',
            self::FILLED => 'Vacante cubierta con un candidato',
            self::VACANT => 'Vacante declarada desierta',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::AVAILABLE => 'success',
            self::IN_PROCESS => 'info',
            self::FILLED => 'primary',
            self::VACANT => 'warning',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
            'badge' => $case->badge(),
        ], self::cases());
    }
}
