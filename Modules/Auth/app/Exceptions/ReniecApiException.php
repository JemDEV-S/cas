<?php

namespace Modules\Auth\Exceptions;

/**
 * Excepción lanzada cuando la API de RENIEC retorna un error
 */
class ReniecApiException extends ReniecException
{
    protected int $httpStatusCode = 500;

    /**
     * Crear excepción desde respuesta HTTP
     */
    public static function fromHttpStatus(int $statusCode, string $responseBody = ''): self
    {
        $message = match ($statusCode) {
            401, 403 => 'Token de API inválido o sin permisos. Verifique su configuración.',
            422 => 'Error de validación en la API. Verifique el formato del DNI.',
            429 => 'Límite de peticiones excedido. Intente nuevamente más tarde.',
            500, 502, 503, 504 => 'El servicio de RENIEC no está disponible temporalmente.',
            default => "Error al consultar RENIEC (Status: {$statusCode})",
        };

        $exception = new self($message);
        $exception->setHttpStatusCode($statusCode >= 500 ? 503 : 500);

        return $exception;
    }

    /**
     * Error de conexión con la API
     */
    public static function connectionError(string $originalMessage): self
    {
        $exception = new self('No se pudo conectar con el servicio de RENIEC. Intente nuevamente.');
        $exception->setHttpStatusCode(503);

        return $exception;
    }
}
