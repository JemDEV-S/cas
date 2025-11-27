<?php

namespace Modules\JobProfile\Enums;

enum WorkRegimeEnum: string
{
    case CAS = 'cas';
    case D728 = '728';
    case D276 = '276';
    case LOCACION = 'locacion';

    public function label(): string
    {
        return match($this) {
            self::CAS => 'CAS (Contrato Administrativo de Servicios)',
            self::D728 => 'D.Leg. 728 (Régimen Laboral Privado)',
            self::D276 => 'D.Leg. 276 (Régimen Laboral Público)',
            self::LOCACION => 'Locación de Servicios',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::CAS => 'Contrato temporal para el sector público',
            self::D728 => 'Régimen laboral de la actividad privada aplicado al sector público',
            self::D276 => 'Régimen de la carrera administrativa',
            self::LOCACION => 'Contrato de servicios independientes',
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
        ], self::cases());
    }
}
