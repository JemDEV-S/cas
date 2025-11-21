<?php

namespace Modules\Core\Traits;

use Illuminate\Support\Str;

/**
 * HasUuid Trait
 *
 * Proporciona generación automática de UUID para las claves primarias.
 */
trait HasUuid
{
    /**
     * Boot del trait.
     *
     * @return void
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Indica si los IDs son auto-incrementales.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Obtiene el tipo de la clave primaria.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
