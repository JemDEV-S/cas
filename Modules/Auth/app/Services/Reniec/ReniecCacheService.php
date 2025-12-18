<?php

namespace Modules\Auth\Services\Reniec;

use Illuminate\Support\Facades\Cache;
use Modules\Auth\DTOs\ReniecPersonDataDTO;

/**
 * Servicio de caché para datos de RENIEC
 * Solo cachea respuestas exitosas
 */
class ReniecCacheService
{
    private const CACHE_PREFIX = 'reniec:dni:';
    private const CACHE_TAG = 'reniec';

    public function __construct(
        private readonly bool $enabled,
        private readonly int $ttl,
    ) {}

    /**
     * Obtener datos de una persona desde caché
     *
     * @param string $dni DNI de 8 dígitos
     * @return ReniecPersonDataDTO|null
     */
    public function get(string $dni): ?ReniecPersonDataDTO
    {
        if (!$this->enabled) {
            return null;
        }

        $cacheKey = $this->getCacheKey($dni);

        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return null;
        }

        // Reconstruir DTO desde array cacheado
        return new ReniecPersonDataDTO(
            dni: $cached['dni'],
            nombres: $cached['nombres'],
            apellidoPaterno: $cached['apellido_paterno'],
            apellidoMaterno: $cached['apellido_materno'],
            nombreCompleto: $cached['nombre_completo'],
            genero: $cached['genero'],
            fechaNacimiento: $cached['fecha_nacimiento'],
            codigoVerificacion: $cached['codigo_verificacion'],
        );
    }

    /**
     * Guardar datos de una persona en caché
     *
     * @param string $dni DNI de 8 dígitos
     * @param ReniecPersonDataDTO $personData Datos a cachear
     */
    public function put(string $dni, ReniecPersonDataDTO $personData): void
    {
        if (!$this->enabled) {
            return;
        }

        $cacheKey = $this->getCacheKey($dni);

        Cache::put(
            $cacheKey,
            $personData->toArray(),
            $this->ttl
        );
    }

    /**
     * Eliminar del caché los datos de un DNI específico
     *
     * @param string $dni DNI de 8 dígitos
     */
    public function forget(string $dni): void
    {
        $cacheKey = $this->getCacheKey($dni);

        Cache::forget($cacheKey);
    }

    /**
     * Limpiar todo el caché de RENIEC
     * NOTA: Sin tags, esto limpia TODA la caché. Usar con precaución.
     */
    public function flush(): void
    {
        // Sin soporte de tags, buscar y eliminar todas las claves con el prefijo
        // ADVERTENCIA: Esto puede ser lento con muchas entradas
        // Alternativa: Solo usar forget() individualmente

        // Por ahora, simplemente limpiar todo el caché
        // En producción con Redis, cambiar a tags
        Cache::flush();
    }

    /**
     * Obtener o ejecutar callback con lock para evitar cache stampede
     *
     * @param string $dni DNI de 8 dígitos
     * @param callable $callback Función que obtiene los datos
     * @return ReniecPersonDataDTO|null
     */
    public function remember(string $dni, callable $callback): ?ReniecPersonDataDTO
    {
        if (!$this->enabled) {
            return $callback();
        }

        // Intentar obtener de caché
        $cached = $this->get($dni);
        if ($cached !== null) {
            return $cached;
        }

        // Usar lock para evitar múltiples peticiones simultáneas a la API
        $lockKey = "lock:" . $this->getCacheKey($dni);

        return Cache::lock($lockKey, 10)->get(function () use ($dni, $callback) {
            // Verificar de nuevo por si otro proceso ya lo cacheó
            $cached = $this->get($dni);
            if ($cached !== null) {
                return $cached;
            }

            // Ejecutar callback para obtener datos frescos
            $result = $callback();

            // Cachear solo si el resultado es exitoso
            if ($result instanceof ReniecPersonDataDTO) {
                $this->put($dni, $result);
            }

            return $result;
        });
    }

    /**
     * Generar clave de caché para un DNI
     *
     * @param string $dni DNI de 8 dígitos
     * @return string
     */
    private function getCacheKey(string $dni): string
    {
        return self::CACHE_PREFIX . $dni;
    }
}
