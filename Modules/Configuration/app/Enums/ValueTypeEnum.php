<?php

namespace Modules\Configuration\Enums;

enum ValueTypeEnum: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case DECIMAL = 'decimal';  // ✅ AGREGAR ESTO
    case BOOLEAN = 'boolean';
    case JSON = 'json';
    case ARRAY = 'array';
    case EMAIL = 'email';
    case URL = 'url';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TEXT = 'text';
    case FILE = 'file';  // ✅ AGREGAR ESTO

    /**
     * Obtener etiqueta legible
     */
    public function label(): string
    {
        return match ($this) {
            self::STRING => 'Texto',
            self::INTEGER => 'Número Entero',
            self::FLOAT => 'Número Decimal',
            self::DECIMAL => 'Decimal',  // ✅ AGREGAR ESTO
            self::BOOLEAN => 'Booleano',
            self::JSON => 'JSON',
            self::ARRAY => 'Array',
            self::EMAIL => 'Correo Electrónico',
            self::URL => 'URL',
            self::DATE => 'Fecha',
            self::DATETIME => 'Fecha y Hora',
            self::TEXT => 'Texto Largo',
            self::FILE => 'Archivo',  // ✅ AGREGAR ESTO
        };
    }

    /**
     * Obtener todas las opciones para select
     */
    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
