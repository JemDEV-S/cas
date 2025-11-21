<?php

namespace Modules\Configuration\Enums;

enum ValueTypeEnum: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    case BOOLEAN = 'boolean';
    case JSON = 'json';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TEXT = 'text';
    case FILE = 'file';

    public function label(): string
    {
        return match ($this) {
            self::STRING => 'Cadena de texto',
            self::INTEGER => 'Número entero',
            self::DECIMAL => 'Número decimal',
            self::BOOLEAN => 'Booleano',
            self::JSON => 'JSON',
            self::DATE => 'Fecha',
            self::DATETIME => 'Fecha y hora',
            self::TEXT => 'Texto largo',
            self::FILE => 'Archivo',
        };
    }
}
