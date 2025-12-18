<?php

namespace Modules\Auth\Exceptions;

/**
 * Excepción lanzada cuando la validación del código verificador falla
 */
class ReniecValidationException extends ReniecException
{
    protected int $httpStatusCode = 422;

    public function __construct(string $message = 'El código verificador del DNI no coincide. Verifique los datos en su documento.')
    {
        parent::__construct($message);
    }

    /**
     * Validación de código verificador fallida
     */
    public static function invalidCheckDigit(): self
    {
        return new self('El código verificador del DNI no coincide. Verifique los datos en su documento.');
    }

    /**
     * Validación de formato de DNI fallida
     */
    public static function invalidDniFormat(): self
    {
        return new self('DNI debe contener exactamente 8 dígitos numéricos.');
    }
}
