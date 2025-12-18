<?php

namespace Modules\Auth\Exceptions;

/**
 * Excepción lanzada cuando no se encuentra información para un DNI
 */
class ReniecNotFoundException extends ReniecException
{
    protected int $httpStatusCode = 404;

    public function __construct(string $dni)
    {
        parent::__construct("No se encontraron datos para el DNI proporcionado. Verifique el número.");
    }
}
