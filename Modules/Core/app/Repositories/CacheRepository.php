<?php

namespace Modules\Core\Repositories;

use Illuminate\Support\Facades\Cache;

/**
 * Cache Repository
 *
 * Repositorio para gestión de caché.
 */
class CacheRepository
{
    /**
     * TTL por defecto (en segundos).
     *
     * @var int
     */
    protected int $defaultTtl = 3600;

    /**
     * Prefijo para las claves de caché.
     *
     * @var string
     */
    protected string $prefix = 'app';

    /**
     * Constructor.
     *
     * @param string|null $prefix
     * @param int|null $defaultTtl
     */
    public function __construct(?string $prefix = null, ?int $defaultTtl = null)
    {
        if ($prefix) {
            $this->prefix = $prefix;
        }

        if ($defaultTtl) {
            $this->defaultTtl = $defaultTtl;
        }
    }

    /**
     * Genera una clave de caché con prefijo.
     *
     * @param string $key
     * @return string
     */
    protected function makeKey(string $key): string
    {
        return $this->prefix . ':' . $key;
    }

    /**
     * Obtiene un valor de caché.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Cache::get($this->makeKey($key), $default);
    }

    /**
     * Almacena un valor en caché.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function put(string $key, $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        return Cache::put($this->makeKey($key), $value, $ttl);
    }

    /**
     * Almacena un valor en caché para siempre.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function forever(string $key, $value): bool
    {
        return Cache::forever($this->makeKey($key), $value);
    }

    /**
     * Obtiene un valor o lo almacena si no existe.
     *
     * @param string $key
     * @param int|null $ttl
     * @param callable $callback
     * @return mixed
     */
    public function remember(string $key, ?int $ttl, callable $callback)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        return Cache::remember($this->makeKey($key), $ttl, $callback);
    }

    /**
     * Obtiene un valor o lo almacena para siempre si no existe.
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function rememberForever(string $key, callable $callback)
    {
        return Cache::rememberForever($this->makeKey($key), $callback);
    }

    /**
     * Verifica si existe una clave en caché.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Cache::has($this->makeKey($key));
    }

    /**
     * Elimina un valor de caché.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        return Cache::forget($this->makeKey($key));
    }

    /**
     * Elimina múltiples valores de caché.
     *
     * @param array $keys
     * @return void
     */
    public function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Limpia todo el caché.
     *
     * @return bool
     */
    public function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * Incrementa un valor en caché.
     *
     * @param string $key
     * @param int $value
     * @return int|bool
     */
    public function increment(string $key, int $value = 1)
    {
        return Cache::increment($this->makeKey($key), $value);
    }

    /**
     * Decrementa un valor en caché.
     *
     * @param string $key
     * @param int $value
     * @return int|bool
     */
    public function decrement(string $key, int $value = 1)
    {
        return Cache::decrement($this->makeKey($key), $value);
    }

    /**
     * Obtiene múltiples valores de caché.
     *
     * @param array $keys
     * @return array
     */
    public function many(array $keys): array
    {
        $prefixedKeys = array_map(fn($key) => $this->makeKey($key), $keys);
        return Cache::many($prefixedKeys);
    }

    /**
     * Almacena múltiples valores en caché.
     *
     * @param array $values
     * @param int|null $ttl
     * @return bool
     */
    public function putMany(array $values, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $prefixedValues = [];

        foreach ($values as $key => $value) {
            $prefixedValues[$this->makeKey($key)] = $value;
        }

        return Cache::putMany($prefixedValues, $ttl);
    }

    /**
     * Almacena un valor solo si no existe.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function add(string $key, $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        return Cache::add($this->makeKey($key), $value, $ttl);
    }

    /**
     * Obtiene y elimina un valor de caché.
     *
     * @param string $key
     * @return mixed
     */
    public function pull(string $key)
    {
        return Cache::pull($this->makeKey($key));
    }

    /**
     * Establece el TTL por defecto.
     *
     * @param int $ttl
     * @return self
     */
    public function setDefaultTtl(int $ttl): self
    {
        $this->defaultTtl = $ttl;
        return $this;
    }

    /**
     * Establece el prefijo.
     *
     * @param string $prefix
     * @return self
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }
}
