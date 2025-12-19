<?php

namespace Modules\Auth\Exceptions;

/**
 * Excepción lanzada cuando el servicio de RENIEC no está disponible o configurado
 */
class ReniecServiceUnavailableException extends ReniecException
{
    protected int $httpStatusCode = 503;

    public function __construct()
    {
        parent::__construct('El servicio de validación de DNI no está disponible en este momento.');
    }
}
