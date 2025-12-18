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
}
