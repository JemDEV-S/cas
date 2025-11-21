<?php

namespace Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Repository
 *
 * Implementa el patrón repositorio base para operaciones comunes de base de datos.
 */
abstract class BaseRepository
{
    /**
     * El modelo asociado al repositorio.
     *
     * @var Model
     */
    protected $model;

    /**
     * Constructor del repositorio.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Obtiene todos los registros.
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->get($columns);
    }

    /**
     * Obtiene registros paginados.
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Encuentra un registro por ID.
     *
     * @param mixed $id
     * @param array $columns
     * @return Model|null
     */
    public function find($id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Encuentra un registro por ID o falla.
     *
     * @param mixed $id
     * @param array $columns
     * @return Model
     */
    public function findOrFail($id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Encuentra un registro por columna.
     *
     * @param string $column
     * @param mixed $value
     * @param array $columns
     * @return Model|null
     */
    public function findBy(string $column, $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($column, $value)->first($columns);
    }

    /**
     * Encuentra registros por columna.
     *
     * @param string $column
     * @param mixed $value
     * @param array $columns
     * @return Collection
     */
    public function findAllBy(string $column, $value, array $columns = ['*']): Collection
    {
        return $this->model->where($column, $value)->get($columns);
    }

    /**
     * Crea un nuevo registro.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Actualiza un registro.
     *
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        $model = $this->findOrFail($id);
        return $model->update($data);
    }

    /**
     * Elimina un registro.
     *
     * @param mixed $id
     * @return bool
     */
    public function delete($id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    /**
     * Elimina múltiples registros.
     *
     * @param array $ids
     * @return int
     */
    public function deleteMany(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * Cuenta registros.
     *
     * @param array $where
     * @return int
     */
    public function count(array $where = []): int
    {
        $query = $this->model->query();

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        return $query->count();
    }

    /**
     * Verifica si existe un registro.
     *
     * @param string $column
     * @param mixed $value
     * @return bool
     */
    public function exists(string $column, $value): bool
    {
        return $this->model->where($column, $value)->exists();
    }

    /**
     * Obtiene el primer registro.
     *
     * @param array $columns
     * @return Model|null
     */
    public function first(array $columns = ['*']): ?Model
    {
        return $this->model->first($columns);
    }

    /**
     * Crea o actualiza un registro.
     *
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * Restaura un registro soft deleted.
     *
     * @param mixed $id
     * @return bool
     */
    public function restore($id): bool
    {
        $model = $this->model->withTrashed()->findOrFail($id);
        return $model->restore();
    }

    /**
     * Elimina permanentemente un registro.
     *
     * @param mixed $id
     * @return bool
     */
    public function forceDelete($id): bool
    {
        $model = $this->model->withTrashed()->findOrFail($id);
        return $model->forceDelete();
    }

    /**
     * Obtiene el modelo asociado.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Establece el modelo asociado.
     *
     * @param Model $model
     * @return self
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Resetea el query builder.
     *
     * @return self
     */
    public function resetModel(): self
    {
        $this->model = $this->model->newQuery()->getModel();
        return $this;
    }
}
