<?php

namespace Modules\Core\Traits;

/**
 * Filterable Trait
 *
 * Proporciona funcionalidad de filtrado avanzado en los modelos.
 */
trait Filterable
{
    /**
     * Scope para aplicar filtros.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        foreach ($filters as $column => $value) {
            if (empty($value) || !$this->isFilterable($column)) {
                continue;
            }

            $this->applyFilter($query, $column, $value);
        }

        return $query;
    }

    /**
     * Aplica un filtro específico.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param mixed $value
     * @return void
     */
    protected function applyFilter($query, string $column, $value): void
    {
        // Filtro por relación
        if (str_contains($column, '.')) {
            [$relation, $relationColumn] = explode('.', $column);
            $query->whereHas($relation, function ($relationQuery) use ($relationColumn, $value) {
                $this->applySimpleFilter($relationQuery, $relationColumn, $value);
            });
            return;
        }

        // Filtro simple
        $this->applySimpleFilter($query, $column, $value);
    }

    /**
     * Aplica un filtro simple.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param mixed $value
     * @return void
     */
    protected function applySimpleFilter($query, string $column, $value): void
    {
        if (is_array($value)) {
            // Filtro IN
            $query->whereIn($column, $value);
        } elseif (is_bool($value)) {
            // Filtro booleano
            $query->where($column, $value);
        } elseif (preg_match('/^(\>|\<|\>\=|\<\=)\s*(.+)$/', $value, $matches)) {
            // Filtro de comparación
            $query->where($column, $matches[1], $matches[2]);
        } elseif (str_contains($value, '*')) {
            // Filtro LIKE con wildcards
            $likeValue = str_replace('*', '%', $value);
            $query->where($column, 'ILIKE', $likeValue);
        } else {
            // Filtro de igualdad
            $query->where($column, $value);
        }
    }

    /**
     * Verifica si una columna es filtrable.
     *
     * @param string $column
     * @return bool
     */
    protected function isFilterable(string $column): bool
    {
        $filterableColumns = $this->getFilterableColumns();

        // Si la columna contiene un punto, extraer la parte de la columna
        if (str_contains($column, '.')) {
            [, $actualColumn] = explode('.', $column);
            return in_array($column, $filterableColumns) || in_array($actualColumn, $filterableColumns);
        }

        return in_array($column, $filterableColumns);
    }

    /**
     * Obtiene las columnas que pueden ser filtradas.
     *
     * @return array
     */
    protected function getFilterableColumns(): array
    {
        return property_exists($this, 'filterable') ? $this->filterable : [];
    }
}
