<?php

namespace Modules\Core\Traits;

/**
 * Searchable Trait
 *
 * Proporciona funcionalidad de búsqueda full-text en los modelos.
 */
trait Searchable
{
    /**
     * Scope para realizar búsquedas globales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        $searchableColumns = $this->getSearchableColumns();

        return $query->where(function ($q) use ($search, $searchableColumns) {
            foreach ($searchableColumns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';

                if (str_contains($column, '.')) {
                    // Búsqueda en relaciones
                    [$relation, $relationColumn] = explode('.', $column);
                    $q->orWhereHas($relation, function ($relationQuery) use ($relationColumn, $search) {
                        $relationQuery->where($relationColumn, 'ILIKE', "%{$search}%");
                    });
                } else {
                    // Búsqueda en columnas del modelo
                    $q->$method($column, 'ILIKE', "%{$search}%");
                }
            }
        });
    }

    /**
     * Obtiene las columnas en las que se puede buscar.
     *
     * @return array
     */
    protected function getSearchableColumns(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : ['*'];
    }
}
