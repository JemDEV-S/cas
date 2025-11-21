<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Base Soft Delete Model
 *
 * Modelo abstracto base que incluye funcionalidad de soft deletes
 * para permitir la eliminación lógica de registros.
 */
abstract class BaseSoftDelete extends BaseModel
{
    use SoftDeletes;

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope para obtener solo registros activos (no eliminados).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope para obtener solo registros eliminados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyDeleted($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    /**
     * Verifica si el modelo está eliminado.
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return !is_null($this->deleted_at);
    }

    /**
     * Verifica si el modelo está activo.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return is_null($this->deleted_at);
    }

    /**
     * Restaura un modelo eliminado y ejecuta callbacks.
     *
     * @return bool|null
     */
    public function restore()
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $result = parent::restore();

        $this->fireModelEvent('restored', false);

        return $result;
    }
}
