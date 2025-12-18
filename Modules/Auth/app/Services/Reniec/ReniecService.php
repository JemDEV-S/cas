<?php

namespace Modules\Auth\Services\Reniec;

use Modules\Auth\DTOs\ReniecPersonDataDTO;
use Modules\Auth\DTOs\ReniecValidationResultDTO;
use Modules\Auth\Exceptions\ReniecServiceUnavailableException;
use Modules\Auth\Exceptions\ReniecValidationException;
use Modules\Auth\Exceptions\ReniecNotFoundException;

/**
 * Servicio principal de RENIEC
 * Orquesta todos los servicios relacionados con validación de DNI
 */
class ReniecService
{
    public function __construct(
        private readonly bool $enabled,
        private readonly ReniecApiClient $apiClient,
        private readonly ReniecValidator $validator,
        private readonly ReniecCacheService $cache,
    ) {}

    /**
     * Verificar si el servicio está habilitado
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Obtener datos de una persona por DNI
     * Usa caché si está disponible
     *
     * @param string $dni DNI de 8 dígitos
     * @return ReniecPersonDataDTO
     * @throws ReniecServiceUnavailableException
     * @throws ReniecNotFoundException
     * @throws ReniecApiException
     */
    public function findByDni(string $dni): ReniecPersonDataDTO
    {
        $this->ensureServiceIsEnabled();

        // Usar caché con lock para evitar cache stampede
        return $this->cache->remember($dni, function () use ($dni) {
            return $this->apiClient->queryDni($dni);
        });
    }

    /**
     * Validar DNI con código verificador
     *
     * ESTRATEGIA: Validación basada en la API como fuente de verdad
     *
     * Flujo de validación:
     * 1. Consultar datos en RENIEC (API)
     * 2. Comparar código verificador del usuario con el de la API
     * 3. Si coinciden → Válido
     *
     * @param string $dni DNI de 8 dígitos
     * @param string $checkDigit Código verificador proporcionado por el usuario
     * @return ReniecValidationResultDTO
     */
    public function validateWithCheckDigit(string $dni, string $checkDigit): ReniecValidationResultDTO
    {
        try {
            $this->ensureServiceIsEnabled();

            // Paso 1: Consultar datos en RENIEC (API es la fuente de verdad)
            $personData = $this->findByDni($dni);

            // Paso 2: Validar código verificador con el de la API
            if (!$this->validator->validateWithPersonData($personData, $checkDigit)) {
                return ReniecValidationResultDTO::failure(
                    'El código verificador del DNI no coincide. Verifique los datos en su documento.'
                );
            }

            // Validación exitosa
            return ReniecValidationResultDTO::success($personData);

        } catch (ReniecNotFoundException $e) {
            return ReniecValidationResultDTO::failure($e->getMessage());
        } catch (ReniecValidationException $e) {
            return ReniecValidationResultDTO::failure($e->getMessage());
        } catch (\Exception $e) {
            return ReniecValidationResultDTO::failure(
                'Error al validar DNI. Por favor, intente nuevamente.'
            );
        }
    }

    /**
     * Consultar DNI sin validación de código verificador
     * Útil para pre-llenar formularios
     *
     * @param string $dni DNI de 8 dígitos
     * @return ReniecPersonDataDTO|null
     */
    public function consultDni(string $dni): ?ReniecPersonDataDTO
    {
        try {
            $this->ensureServiceIsEnabled();
            return $this->findByDni($dni);
        } catch (ReniecNotFoundException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Limpiar caché de un DNI específico
     *
     * @param string $dni DNI de 8 dígitos
     */
    public function clearCache(string $dni): void
    {
        $this->cache->forget($dni);
    }

    /**
     * Limpiar todo el caché de RENIEC
     */
    public function flushCache(): void
    {
        $this->cache->flush();
    }

    /**
     * Asegurar que el servicio está habilitado
     *
     * @throws ReniecServiceUnavailableException
     */
    private function ensureServiceIsEnabled(): void
    {
        if (!$this->enabled) {
            throw new ReniecServiceUnavailableException();
        }
    }
}
