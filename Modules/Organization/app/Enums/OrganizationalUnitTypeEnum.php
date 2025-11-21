<?php

namespace Modules\Organization\Enums;

enum OrganizationalUnitTypeEnum: string
{
    case ORGANO = 'organo';
    case AREA = 'area';
    case SUB_UNIDAD = 'sub_unidad';

    public function label(): string
    {
        return match($this) {
            self::ORGANO => 'Órgano',
            self::AREA => 'Área',
            self::SUB_UNIDAD => 'Sub Unidad',
        };
    }

    public function level(): int
    {
        return match($this) {
            self::ORGANO => 1,
            self::AREA => 2,
            self::SUB_UNIDAD => 3,
        };
    }
}
