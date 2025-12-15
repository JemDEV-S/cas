<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Services\ReniecService;
use Illuminate\Support\Facades\Log;

class ReniecValidationController extends Controller
{
    public function __construct(
        protected ReniecService $reniecService
    ) {}

    /**
     * Validar DNI con RENIEC
     *
     * POST /api/auth/validate-dni
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateDni(Request $request): JsonResponse
    {
        Log::info('ReniecValidationController: Petición recibida', [
            'dni' => $request->input('dni'),
            'codigo_verificador' => $request->input('codigo_verificador'),
            'all_data' => $request->all()
        ]);

        try {
            // Validar entrada
            $validated = $request->validate([
                'dni' => ['required', 'string', 'size:8', 'regex:/^[0-9]{8}$/'],
                'codigo_verificador' => ['nullable', 'string', 'size:1'],
            ]);

            Log::info('ReniecValidationController: Validación de entrada exitosa', $validated);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ReniecValidationController: Error de validación de entrada', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Verificar si el servicio está habilitado
            if (!$this->reniecService->isEnabled()) {
                Log::warning('ReniecValidationController: Servicio RENIEC no disponible');
                return response()->json([
                    'success' => false,
                    'message' => 'El servicio de validación de DNI no está disponible en este momento.',
                    'data' => null
                ], 503);
            }

            $dni = $request->input('dni');
            $codigoVerificador = $request->input('codigo_verificador');

            Log::info('ReniecValidationController: Llamando a validarParaRegistro', [
                'dni' => $dni,
                'tiene_codigo' => !empty($codigoVerificador)
            ]);

            // Validar con RENIEC
            $resultado = $this->reniecService->validarParaRegistro($dni, $codigoVerificador);

            Log::info('ReniecValidationController: Resultado de validación', [
                'valid' => $resultado['valid'],
                'message' => $resultado['message'],
                'has_data' => isset($resultado['data'])
            ]);

            if ($resultado['valid']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['message'],
                    'data' => $resultado['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message'],
                    'data' => null
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('ReniecValidationController: Error en validación de DNI', [
                'dni' => $request->input('dni'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al validar DNI. Por favor, intente nuevamente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consultar solo datos de DNI (sin código verificador)
     *
     * GET /api/auth/consultar-dni/{dni}
     *
     * @param string $dni
     * @return JsonResponse
     */
    public function consultarDni(string $dni): JsonResponse
    {
        Log::info('ReniecValidationController: consultarDni', ['dni' => $dni]);

        // Validar formato
        if (!preg_match('/^\d{8}$/', $dni)) {
            return response()->json([
                'success' => false,
                'message' => 'DNI debe contener exactamente 8 dígitos numéricos.',
                'data' => null
            ], 422);
        }

        try {
            if (!$this->reniecService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El servicio de consulta de DNI no está disponible.',
                    'data' => null
                ], 503);
            }

            $datos = $this->reniecService->getDatosParaRegistro($dni);

            if ($datos) {
                return response()->json([
                    'success' => true,
                    'message' => 'DNI encontrado exitosamente',
                    'data' => $datos
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos para el DNI proporcionado.',
                    'data' => null
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('ReniecValidationController: Error en consulta de DNI', [
                'dni' => $dni,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar DNI.',
                'error' => $e->getMessage()
            ], 500);
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

        Log::info('ReniecValidationController: checkStatus', ['enabled' => $enabled]);

        return response()->json([
            'enabled' => $enabled,
            'message' => $enabled
                ? 'Servicio de RENIEC disponible'
                : 'Servicio de RENIEC no configurado o deshabilitado'
        ]);
    }
}
