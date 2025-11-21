<?php

namespace Modules\User\Enums;

enum GenderEnum: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';
    case PREFER_NOT_TO_SAY = 'prefer_not_to_say';

    public function label(): string
    {
        return match($this) {
            self::MALE => 'Masculino',
            self::FEMALE => 'Femenino',
            self::OTHER => 'Otro',
            self::PREFER_NOT_TO_SAY => 'Prefiero no decir',
        };
    }
}
