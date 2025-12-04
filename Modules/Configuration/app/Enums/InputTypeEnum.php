<?php

namespace Modules\Configuration\Enums;

enum InputTypeEnum: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case TEXTAREA = 'textarea';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case BOOLEAN = 'boolean';  // ✅ AGREGAR ESTO (igual que checkbox pero más semántico)
    case RADIO = 'radio';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case EMAIL = 'email';
    case URL = 'url';
    case COLOR = 'color';
    case FILE = 'file';
    case JSON_EDITOR = 'json_editor';

    /**
     * Obtener etiqueta legible
     */
    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Campo de Texto',
            self::NUMBER => 'Campo Numérico',
            self::TEXTAREA => 'Área de Texto',
            self::SELECT => 'Lista Desplegable',
            self::CHECKBOX => 'Casilla de Verificación',
            self::BOOLEAN => 'Casilla de Verificación',  // ✅ AGREGAR ESTO
            self::RADIO => 'Botones de Radio',
            self::DATE => 'Selector de Fecha',
            self::DATETIME => 'Selector de Fecha y Hora',
            self::EMAIL => 'Campo de Email',
            self::URL => 'Campo de URL',
            self::COLOR => 'Selector de Color',
            self::FILE => 'Subida de Archivo',
            self::JSON_EDITOR => 'Editor JSON',
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
