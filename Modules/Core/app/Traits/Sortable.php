<?php

namespace Modules\Core\Traits;

/**
 * Sortable Trait
 *
 * Proporciona funcionalidad de ordenamiento dinámico en los modelos.
 */
trait Sortable
{
    /**
     * Scope para ordenamiento dinámico.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $column
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort($query, ?string $column = null, string $direction = 'asc')
    {
        if (empty($column)) {
            return $query;
        }

        $sortableColumns = $this->getSortableColumns();

        if (!in_array($column, $sortableColumns)) {
            return $query;
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        if (str_contains($column, '.')) {
            // Ordenamiento por relaciones
            [$relation, $relationColumn] = explode('.', $column);
            return $query->orderByRelation($relation, $relationColumn, $direction);
        }

        return $query->orderBy($column, $direction);
    }

    /**
     * Scope para ordenamiento múltiple.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $sorts
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMultiSort($query, array $sorts)
    {
        foreach ($sorts as $column => $direction) {
            $query->sort($column, $direction);
        }

        return $query;
    }

    /**
     * Obtiene las columnas que pueden ser ordenadas.
     *
     * @return array
     */
    protected function getSortableColumns(): array
    {
        return property_exists($this, 'sortable') ? $this->sortable : [];
    }
}
