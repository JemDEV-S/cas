<?php

namespace Modules\Auth\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait para estandarizar las respuestas JSON de la API
 */
trait ApiResponses
{
    /**
     * Respuesta de éxito
     *
     * @param mixed $data Datos a retornar
     * @param string $message Mensaje de éxito
     * @param int $status Código HTTP
     * @return JsonResponse
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operación exitosa',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Respuesta de error
     *
     * @param string $message Mensaje de error
     * @param int $status Código HTTP
     * @param mixed $errors Errores adicionales (opcional)
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message,
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Respuesta de error de validación
     *
     * @param string $message Mensaje de error
     * @param array $errors Errores de validación
     * @return JsonResponse
     */
    protected function validationErrorResponse(
        string $message = 'Datos de entrada inválidos',
        array $errors = []
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Respuesta de recurso no encontrado
     *
     * @param string $message Mensaje de error
     * @return JsonResponse
     */
    protected function notFoundResponse(
        string $message = 'Recurso no encontrado'
    ): JsonResponse {
        return $this->errorResponse($message, 404);
    }

    /**
     * Respuesta de servicio no disponible
     *
     * @param string $message Mensaje de error
     * @return JsonResponse
     */
    protected function serviceUnavailableResponse(
        string $message = 'Servicio no disponible en este momento'
    ): JsonResponse {
        return $this->errorResponse($message, 503);
    }

    /**
     * Respuesta desde una excepción de RENIEC
     *
     * @param \Modules\Auth\Exceptions\ReniecException $exception
     * @return JsonResponse
     */
    protected function reniecExceptionResponse($exception): JsonResponse
    {
        return $this->errorResponse(
            $exception->getMessage(),
            $exception->getHttpStatusCode()
        );
    }
}
