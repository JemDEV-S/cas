<?php

namespace Modules\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Modules\Core\ValueObjects\ExperienceDuration;

class ExperienceDurationCast implements CastsAttributes
{
    // De la Base de Datos al Modelo (Decimal -> Objeto)
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        return ExperienceDuration::fromDecimal((float) $value);
    }

    // Del Modelo a la Base de Datos (Objeto -> Decimal)
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        // Si ya es una instancia, obtener el decimal
        if ($value instanceof ExperienceDuration) {
            return $value->toDecimal();
        }

        // Si por error pasan un número directo, intentar convertirlo
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        throw new \InvalidArgumentException('El valor debe ser numérico o una instancia de ExperienceDuration');
    }
}