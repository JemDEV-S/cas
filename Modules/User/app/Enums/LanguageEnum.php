<?php

namespace Modules\User\Enums;

enum LanguageEnum: string
{
    case SPANISH = 'es';
    case ENGLISH = 'en';

    public function label(): string
    {
        return match($this) {
            self::SPANISH => 'Español',
            self::ENGLISH => 'English',
        };
    }
}
