<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Base Model
 *
 * Modelo abstracto base que proporciona funcionalidades comunes
 * para todos los modelos del sistema.
 */
abstract class BaseModel extends Model
{
    use HasFactory;

    /**
     * Indica si el modelo debe usar timestamps.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Scope para búsquedas globales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search, array $columns = [])
    {
        if (empty($search)) {
            return $query;
        }

        $searchableColumns = !empty($columns) ? $columns : $this->getSearchableColumns();

        return $query->where(function ($q) use ($search, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                $q->orWhere($column, 'ILIKE', "%{$search}%");
            }
        });
    }

    /**
     * Scope para ordenamiento dinámico.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort($query, string $column, string $direction = 'asc')
    {
        if (!in_array($column, $this->getSortableColumns())) {
            return $query;
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($column, $direction);
    }

    /**
     * Obtiene las columnas que pueden ser buscadas.
     *
     * @return array
     */
    protected function getSearchableColumns(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : [];
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

    /**
     * Obtiene el nombre de la tabla del modelo.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->getTable();
    }
}
