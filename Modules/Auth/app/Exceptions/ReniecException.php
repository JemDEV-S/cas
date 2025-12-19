<?php

namespace Modules\Auth\Exceptions;

use Exception;

/**
 * Excepción base para todos los errores relacionados con RENIEC
 */
class ReniecException extends Exception
{
    /**
     * Código HTTP sugerido para la respuesta
     */
    protected int $httpStatusCode = 500;

    /**
     * Obtener el código HTTP sugerido
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Establecer el código HTTP sugerido
     */
    public function setHttpStatusCode(int $code): self
    {
        $this->httpStatusCode = $code;
        return $this;
    }
}
