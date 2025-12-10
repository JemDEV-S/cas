<?php

namespace Modules\Evaluation\Enums;

enum ScoreTypeEnum: string
{
    case NUMERIC = 'NUMERIC';           // Numérico (0-20, 0-100, etc.)
    case PERCENTAGE = 'PERCENTAGE';     // Porcentaje (0-100%)
    case QUALITATIVE = 'QUALITATIVE';   // Cualitativo (Excelente, Bueno, Regular, etc.)

    /**
     * Obtener el label legible
     */
    public function label(): string
    {
        return match($this) {
            self::NUMERIC => 'Numérico',
            self::PERCENTAGE => 'Porcentaje',
            self::QUALITATIVE => 'Cualitativo',
        };
    }

    /**
     * Obtener todos los valores como array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtener opciones para select
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}