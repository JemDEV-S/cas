<?php

namespace Modules\Auth\Services\Reniec;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Modules\Auth\DTOs\ReniecPersonDataDTO;
use Modules\Auth\Exceptions\ReniecApiException;
use Modules\Auth\Exceptions\ReniecNotFoundException;

/**
 * Cliente HTTP para la API de PeruDevs (RENIEC)
 * Responsabilidad única: Comunicación con la API externa
 */
class ReniecApiClient
{
    public function __construct(
        private readonly string $apiUrl,
        private readonly string $apiToken,
        private readonly int $timeout,
        private readonly int $retryTimes,
        private readonly int $retrySleep,
    ) {}

    /**
     * Consultar datos de una persona por DNI
     *
     * @param string $dni DNI de 8 dígitos
     * @return ReniecPersonDataDTO
     * @throws ReniecNotFoundException
     * @throws ReniecApiException
     */
    public function queryDni(string $dni): ReniecPersonDataDTO
    {
        $fullUrl = rtrim($this->apiUrl, '/') . '/dni/complete';

        try {
            $httpClient = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep, function ($exception, $request) {
                    // Solo reintentar en errores de red o 5xx
                    return $exception instanceof ConnectionException ||
                           ($exception->response && $exception->response->status() >= 500);
                })
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]);

            // TEMPORAL: Deshabilitar verificación SSL SOLO en desarrollo local
            // TODO: Configurar certificados SSL en producción
            if (config('app.env') === 'local') {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->get($fullUrl, [
                'document' => $dni,
                'key' => $this->apiToken,
            ]);

            // Manejar respuestas según código de estado
            return $this->handleResponse($response, $dni);

        } catch (ConnectionException $e) {
            Log::error('RENIEC API: Error de conexión', [
                'dni_masked' => $this->maskDni($dni),
                'error' => $e->getMessage(),
            ]);

            throw ReniecApiException::connectionError($e->getMessage());
        }
    }

    /**
     * Manejar la respuesta de la API
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $dni
     * @return ReniecPersonDataDTO
     * @throws ReniecNotFoundException
     * @throws ReniecApiException
     */
    private function handleResponse($response, string $dni): ReniecPersonDataDTO
    {
        $statusCode = $response->status();

        // Respuesta exitosa
        if ($response->successful()) {
            $data = $response->json();

            // Validar estructura de respuesta según documentación PeruDevs
            if (isset($data['estado']) && $data['estado'] === true && isset($data['resultado'])) {
                return ReniecPersonDataDTO::fromApiResponse($data['resultado']);
            }

            // Si estado es false, DNI no encontrado
            if (isset($data['estado']) && $data['estado'] === false) {
                Log::warning('RENIEC API: DNI no encontrado', [
                    'dni_masked' => $this->maskDni($dni),
                    'mensaje' => $data['mensaje'] ?? 'Sin mensaje',
                ]);

                throw new ReniecNotFoundException($dni);
            }

            // Respuesta con formato inesperado
            Log::error('RENIEC API: Respuesta con formato inesperado', [
                'dni_masked' => $this->maskDni($dni),
                'response_preview' => substr(json_encode($data), 0, 200),
            ]);

            throw new ReniecApiException('Formato de respuesta inesperado de la API');
        }

        // DNI no encontrado
        if ($statusCode === 404) {
            throw new ReniecNotFoundException($dni);
        }

        // Otros errores HTTP
        Log::error('RENIEC API: Error HTTP', [
            'dni_masked' => $this->maskDni($dni),
            'status' => $statusCode,
            'response_preview' => substr($response->body(), 0, 200),
        ]);

        throw ReniecApiException::fromHttpStatus($statusCode, $response->body());
    }

    /**
     * Enmascarar DNI para logs (cumplimiento LPDP)
     * Ejemplo: 12345678 -> ****5678
     *
     * @param string $dni
     * @return string
     */
    private function maskDni(string $dni): string
    {
        if (strlen($dni) !== 8) {
            return '****';
        }

        return '****' . substr($dni, -4);
    }
}
