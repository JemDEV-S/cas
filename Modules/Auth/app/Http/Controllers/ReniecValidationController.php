<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Http\Requests\ValidateDniRequest;
use Modules\Auth\Http\Requests\ConsultDniRequest;
use Modules\Auth\Http\Traits\ApiResponses;
use Modules\Auth\Services\Reniec\ReniecService;
use Modules\Auth\Exceptions\ReniecException;
use Illuminate\Support\Facades\Log;

class ReniecValidationController extends Controller
{
    use ApiResponses;

    public function __construct(
        private readonly ReniecService $reniecService
    ) {}

    /**
     * Validar DNI con código verificador
     *
     * POST /api/auth/validate-dni
     *
     * @param ValidateDniRequest $request
     * @return JsonResponse
     */
    public function validateDni(ValidateDniRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $result = $this->reniecService->validateWithCheckDigit(
                $validated['dni'],
                $validated['codigo_verificador']
            );

            if ($result->isValid) {
                return $this->successResponse(
                    $result->personData?->toRegistrationData(),
                    $result->message
                );
            }

            return $this->validationErrorResponse($result->message);

        } catch (ReniecException $e) {
            return $this->reniecExceptionResponse($e);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al validar DNI. Por favor, intente nuevamente.',
                500
            );
        }
    }

    /**
     * Consultar DNI sin código verificador
     *
     * GET /api/auth/consultar-dni/{dni}
     *
     * @param ConsultDniRequest $request
     * @return JsonResponse
     */
    public function consultarDni(ConsultDniRequest $request): JsonResponse
    {

        try {
            $dni = $request->getDni();

            $personData = $this->reniecService->consultDni($dni);

            if ($personData) {
                return $this->successResponse(
                    $personData->toRegistrationData(),
                    'DNI encontrado exitosamente'
                );
            }

            return $this->notFoundResponse(
                'No se encontraron datos para el DNI proporcionado.'
            );

        } catch (ReniecException $e) {
            return $this->reniecExceptionResponse($e);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al consultar DNI. Por favor, intente nuevamente.',
                500
            );
        }
    }

    /**
     * Verificar estado del servicio RENIEC
     *
     * GET /api/auth/reniec/status
     *
     * @return JsonResponse
     */
    public function checkStatus(): JsonResponse
    {
        $enabled = $this->reniecService->isEnabled();

        return response()->json([
            'enabled' => $enabled,
            'message' => $enabled
                ? 'Servicio de RENIEC disponible'
                : 'Servicio de RENIEC no configurado o deshabilitado',
        ]);
    }

    /**
     * Diagnóstico del servicio RENIEC
     *
     * GET /api/auth/reniec/diagnostico
     *
     * @return JsonResponse
     */
    public function diagnostico(): JsonResponse
    {
        $diagnostico = [
            'timestamp' => now()->toDateTimeString(),
            'configuracion' => [
                'RENIEC_ENABLED' => config('reniec.enabled'),
                'RENIEC_API_URL' => config('reniec.api.url'),
                'RENIEC_API_TOKEN_CONFIGURADO' => !empty(config('reniec.api.token')),
                'RENIEC_API_TOKEN_LENGTH' => strlen(config('reniec.api.token') ?? ''),
                'RENIEC_API_TIMEOUT' => config('reniec.api.timeout'),
                'RENIEC_DISABLE_SSL_VERIFY' => env('RENIEC_DISABLE_SSL_VERIFY', false),
                'APP_ENV' => config('app.env'),
            ],
            'php' => [
                'version' => PHP_VERSION,
                'curl_enabled' => function_exists('curl_version'),
                'curl_version' => function_exists('curl_version') ? curl_version()['version'] : 'N/A',
                'openssl_enabled' => extension_loaded('openssl'),
            ],
            'test_conexion' => null,
        ];

        // Test de conexión básico
        try {
            $testUrl = config('reniec.api.url') . '/dni/complete';
            $client = \Illuminate\Support\Facades\Http::timeout(5);

            if (env('RENIEC_DISABLE_SSL_VERIFY', false)) {
                $client = $client->withoutVerifying();
            }

            $response = $client->get($testUrl, [
                'document' => '00000000',
                'key' => config('reniec.api.token'),
            ]);

            $diagnostico['test_conexion'] = [
                'status' => 'success',
                'http_code' => $response->status(),
                'response_preview' => substr($response->body(), 0, 200),
            ];
        } catch (\Exception $e) {
            $diagnostico['test_conexion'] = [
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
            ];
        }

        return response()->json($diagnostico);
    }
}
