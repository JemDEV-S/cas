<?php

namespace Modules\User\Enums;

enum ThemeEnum: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
    case SYSTEM = 'system';

    public function label(): string
    {
        return match($this) {
            self::LIGHT => 'Claro',
            self::DARK => 'Oscuro',
            self::SYSTEM => 'Sistema',
        };
    }
}
