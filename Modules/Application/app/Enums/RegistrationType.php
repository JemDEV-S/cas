<?php

namespace Modules\Application\Enums;

enum RegistrationType: string
{
    case PROFESSIONAL_REGISTRY = 'COLEGIATURA';
    case OSCE_CERTIFICATION = 'OSCE_CERTIFICATION';
    case DRIVER_LICENSE = 'DRIVER_LICENSE';

    public function label(): string
    {
        return match($this) {
            self::PROFESSIONAL_REGISTRY => 'Colegiatura Profesional',
            self::OSCE_CERTIFICATION => 'Certificación OSCE',
            self::DRIVER_LICENSE => 'Licencia de Conducir',
        };
    }

    public function requiresExpiry(): bool
    {
        return match($this) {
            self::PROFESSIONAL_REGISTRY => false,
            self::OSCE_CERTIFICATION => true,
            self::DRIVER_LICENSE => true,
        };
    }
}
