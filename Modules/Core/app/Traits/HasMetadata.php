<?php

namespace Modules\Core\Traits;

/**
 * HasMetadata Trait
 *
 * Proporciona funcionalidad para gestionar metadatos JSON en los modelos.
 */
trait HasMetadata
{
    /**
     * Boot del trait.
     *
     * @return void
     */
    protected static function bootHasMetadata(): void
    {
        static::creating(function ($model) {
            if (!isset($model->metadata)) {
                $model->metadata = [];
            }
        });
    }

    /**
     * Inicializa el atributo metadata.
     *
     * @return void
     */
    public function initializeHasMetadata(): void
    {
        $this->casts['metadata'] = 'array';
    }

    /**
     * Obtiene un valor de metadata.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getMeta(string $key, $default = null)
    {
        $metadata = $this->metadata ?? [];
        return data_get($metadata, $key, $default);
    }

    /**
     * Establece un valor de metadata.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setMeta(string $key, $value): self
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Verifica si existe una clave en metadata.
     *
     * @param string $key
     * @return bool
     */
    public function hasMeta(string $key): bool
    {
        $metadata = $this->metadata ?? [];
        return data_get($metadata, $key) !== null;
    }

    /**
     * Elimina una clave de metadata.
     *
     * @param string $key
     * @return self
     */
    public function removeMeta(string $key): self
    {
        $metadata = $this->metadata ?? [];
        data_forget($metadata, $key);
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Obtiene todos los metadatos.
     *
     * @return array
     */
    public function getAllMeta(): array
    {
        return $this->metadata ?? [];
    }

    /**
     * Establece múltiples valores de metadata.
     *
     * @param array $data
     * @param bool $merge
     * @return self
     */
    public function setMetadata(array $data, bool $merge = true): self
    {
        if ($merge) {
            $metadata = array_merge($this->metadata ?? [], $data);
        } else {
            $metadata = $data;
        }

        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Limpia todos los metadatos.
     *
     * @return self
     */
    public function clearMetadata(): self
    {
        $this->metadata = [];
        return $this;
    }
}
