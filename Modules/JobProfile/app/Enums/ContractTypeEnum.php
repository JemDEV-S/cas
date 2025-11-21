<?php

namespace Modules\JobProfile\Enums;

enum ContractTypeEnum: string
{
    case CAS = 'cas';
    case PLAZO_FIJO = 'plazo_fijo';
    case INDEFINIDO = 'indefinido';
    case LOCACION_SERVICIOS = 'locacion_servicios';

    public function label(): string
    {
        return match($this) {
            self::CAS => 'CAS (Contrato Administrativo de Servicios)',
            self::PLAZO_FIJO => 'Plazo Fijo',
            self::INDEFINIDO => 'Indefinido',
            self::LOCACION_SERVICIOS => 'Locación de Servicios',
        };
    }
}
