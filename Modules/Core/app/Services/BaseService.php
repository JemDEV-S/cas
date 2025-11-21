<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Exceptions\BusinessRuleException;

/**
 * Base Service
 *
 * Clase base para todos los servicios del sistema.
 * Proporciona lógica de negocio común y reutilizable.
 */
abstract class BaseService
{
    /**
     * El repositorio asociado al servicio.
     *
     * @var mixed
     */
    protected $repository;

    /**
     * Ejecuta una operación dentro de una transacción.
     *
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    protected function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }

    /**
     * Valida que un modelo exista.
     *
     * @param Model|null $model
     * @param string $message
     * @return Model
     * @throws BusinessRuleException
     */
    protected function ensureExists(?Model $model, string $message = 'Resource not found'): Model
    {
        if (is_null($model)) {
            throw new BusinessRuleException($message);
        }

        return $model;
    }

    /**
     * Valida que un modelo no exista.
     *
     * @param Model|null $model
     * @param string $message
     * @return void
     * @throws BusinessRuleException
     */
    protected function ensureNotExists(?Model $model, string $message = 'Resource already exists'): void
    {
        if (!is_null($model)) {
            throw new BusinessRuleException($message);
        }
    }

    /**
     * Valida una condición de negocio.
     *
     * @param bool $condition
     * @param string $message
     * @return void
     * @throws BusinessRuleException
     */
    protected function validate(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new BusinessRuleException($message);
        }
    }

    /**
     * Obtiene el repositorio.
     *
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
