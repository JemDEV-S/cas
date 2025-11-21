<?php

namespace Modules\Core\Traits;

/**
 * HasStatus Trait
 *
 * Proporciona funcionalidad para gestionar estados en los modelos.
 */
trait HasStatus
{
    /**
     * Boot del trait.
     *
     * @return void
     */
    protected static function bootHasStatus(): void
    {
        static::creating(function ($model) {
            if (!isset($model->status) && defined('static::DEFAULT_STATUS')) {
                $model->status = static::DEFAULT_STATUS;
            }
        });
    }

    /**
     * Scope para filtrar por estado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }

        return $query->where('status', $status);
    }

    /**
     * Scope para obtener registros activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener registros inactivos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Cambia el estado del modelo.
     *
     * @param string $status
     * @return bool
     */
    public function changeStatus(string $status): bool
    {
        if ($this->isValidStatus($status)) {
            $this->status = $status;
            return $this->save();
        }

        return false;
    }

    /**
     * Activa el modelo.
     *
     * @return bool
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Desactiva el modelo.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Verifica si el modelo está activo.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active ?? false;
    }

    /**
     * Verifica si el estado es válido.
     *
     * @param string $status
     * @return bool
     */
    protected function isValidStatus(string $status): bool
    {
        if (defined('static::VALID_STATUSES')) {
            return in_array($status, static::VALID_STATUSES);
        }

        return true;
    }

    /**
     * Obtiene el nombre legible del estado actual.
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        if (defined('static::STATUS_LABELS')) {
            return static::STATUS_LABELS[$this->status] ?? $this->status;
        }

        return $this->status ?? 'unknown';
    }
}
