<?php

namespace Modules\Configuration\Enums;

enum InputTypeEnum: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case SELECT = 'select';
    case TEXTAREA = 'textarea';
    case DATE = 'date';
    case FILE = 'file';
    case COLOR = 'color';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Texto',
            self::NUMBER => 'Número',
            self::BOOLEAN => 'Checkbox',
            self::SELECT => 'Lista de selección',
            self::TEXTAREA => 'Área de texto',
            self::DATE => 'Selector de fecha',
            self::FILE => 'Carga de archivo',
            self::COLOR => 'Selector de color',
        };
    }
}
