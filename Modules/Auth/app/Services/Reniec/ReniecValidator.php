<?php

namespace Modules\Auth\Services\Reniec;

use Modules\Auth\DTOs\ReniecPersonDataDTO;
use Modules\Auth\Exceptions\ReniecValidationException;

/**
 * Servicio de validación de código verificador de DNI
 *
 * ESTRATEGIA: Validación basada ÚNICAMENTE en la API de RENIEC
 * La API es la fuente de verdad oficial. No se usa cálculo local.
 */
class ReniecValidator
{
    /**
     * Validar código verificador contra datos obtenidos de RENIEC (API)
     *
     * Este método confía en la API como fuente de verdad.
     * NO utiliza cálculo local, solo compara con la respuesta oficial de RENIEC.
     *
     * @param ReniecPersonDataDTO $personData Datos de la persona obtenidos de la API
     * @param string $checkDigit Código verificador proporcionado por el usuario
     * @return bool True si el código coincide con el de la API
     */
    public function validateWithPersonData(ReniecPersonDataDTO $personData, string $checkDigit): bool
    {
        // Comparar código verificador del usuario con el de la API (fuente oficial)
        return $personData->hasCheckDigit($checkDigit);
    }

    /**
     * Validar y lanzar excepción si no coincide
     *
     * @param ReniecPersonDataDTO $personData Datos de la persona obtenidos de la API
     * @param string $checkDigit Código verificador proporcionado por el usuario
     * @throws ReniecValidationException Si el código no coincide
     */
    public function validateOrFail(ReniecPersonDataDTO $personData, string $checkDigit): void
    {
        if (!$this->validateWithPersonData($personData, $checkDigit)) {
            throw ReniecValidationException::invalidCheckDigit();
        }
    }
}
