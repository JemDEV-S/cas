<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ReniecService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected bool $enabled;
    protected int $timeout;
    protected int $cacheMinutes;

    public function __construct()
    {
        // Cargar configuración desde .env
        $this->apiUrl = config('auth.reniec.api_url', env('RENIEC_API_URL', 'https://api.perudevs.com/api/v1'));
        $this->apiToken = config('auth.reniec.api_token', env('RENIEC_API_TOKEN'));
        $this->enabled = config('auth.reniec.enabled', env('RENIEC_API_ENABLED', false));
        $this->timeout = config('auth.reniec.timeout', 10);
        $this->cacheMinutes = config('auth.reniec.cache_minutes', 60);
    }

    /**
     * Verificar si el servicio está habilitado
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiToken);
    }

    /**
     * Consultar datos de una persona por DNI usando API PeruDevs
     *
     * @param string $dni Número de DNI de 8 dígitos
     * @return array|null Datos de la persona o null si no se encuentra
     * @throws \Exception Si hay error en la consulta
     */
    public function consultarDni(string $dni): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('RENIEC: Servicio no habilitado o sin API token configurado');
            return null;
        }

        // Validar formato de DNI
        if (!preg_match('/^\d{8}$/', $dni)) {
            throw new \InvalidArgumentException('DNI debe contener exactamente 8 dígitos numéricos');
        }

        // Intentar obtener desde caché
        $cacheKey = "reniec:dni:{$dni}";

        return Cache::remember($cacheKey, $this->cacheMinutes * 60, function () use ($dni) {
            try {
                Log::info("RENIEC: Consultando DNI {$dni}", [
                    'api_url' => $this->apiUrl,
                    'token_length' => strlen($this->apiToken)
                ]);

                // Construir URL según documentación de PeruDevs
                // GET https://api.perudevs.com/api/v1/dni/complete?document=DOCUMENT&key=KEY
                $fullUrl = rtrim($this->apiUrl, '/') . '/dni/complete';

                Log::info("RENIEC: URL construida", [
                    'url' => $fullUrl,
                    'params' => ['document' => $dni]
                ]);

                // Hacer petición según la documentación de PeruDevs
                $response = Http::withoutVerifying()  // ← AGREGAR ESTA LÍNEA
                    ->timeout($this->timeout)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->get($fullUrl, [
                        'document' => $dni,
                        'key' => $this->apiToken
                    ]);

                Log::info("RENIEC: Respuesta recibida", [
                    'dni' => $dni,
                    'status' => $response->status(),
                    'body_preview' => substr($response->body(), 0, 300)
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Verificar respuesta según formato de PeruDevs
                    // estado: true, mensaje: "Encontrado", resultado: {...}
                    if (isset($data['estado']) && $data['estado'] === true && isset($data['resultado'])) {
                        Log::info("RENIEC: DNI {$dni} encontrado exitosamente", [
                            'data' => $data['resultado']
                        ]);
                        return $this->normalizeResponse($data['resultado']);
                    }

                    // Si estado es false
                    if (isset($data['estado']) && $data['estado'] === false) {
                        Log::warning("RENIEC: DNI {$dni} no encontrado", [
                            'mensaje' => $data['mensaje'] ?? 'Sin mensaje'
                        ]);
                        return null;
                    }

                    Log::warning("RENIEC: DNI {$dni} - Respuesta sin formato esperado", [
                        'data' => $data
                    ]);
                    return null;
                }

                // Si es 404, el DNI no existe
                if ($response->status() === 404) {
                    Log::warning("RENIEC: DNI {$dni} no encontrado (404)");
                    return null;
                }

                // Si es 422, problema con los parámetros
                if ($response->status() === 422) {
                    Log::error("RENIEC: Error 422 - Parámetros inválidos", [
                        'dni' => $dni,
                        'response' => $response->body()
                    ]);
                    throw new \Exception("Error de validación en la API. Verifique el formato del DNI.");
                }

                // Si es 401 o 403, problema con el token
                if ($response->status() === 401 || $response->status() === 403) {
                    Log::error("RENIEC: Error {$response->status()} - Token inválido", [
                        'dni' => $dni,
                        'token_preview' => substr($this->apiToken, 0, 20),
                        'response' => $response->body()
                    ]);
                    throw new \Exception("Token de API inválido o sin permisos. Verifique su configuración.");
                }

                // Otros errores
                Log::error("RENIEC: Error en consulta", [
                    'dni' => $dni,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                throw new \Exception("Error al consultar RENIEC (Status: " . $response->status() . ")");

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error("RENIEC: Error de conexión", [
                    'dni' => $dni,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception("No se pudo conectar con el servicio de RENIEC. Intente nuevamente.");
            } catch (\Exception $e) {
                Log::error("RENIEC: Error inesperado", [
                    'dni' => $dni,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Validar DNI con código verificador
     *
     * @param string $dni Número de DNI
     * @param string $codigoVerificador Código verificador del DNI
     * @return bool True si coinciden, false si no
     */
    public function validarDniConCodigo(string $dni, string $codigoVerificador): bool
    {
        // Calcular el código verificador según algoritmo RENIEC
        $codigoCalculado = $this->calcularCodigoVerificador($dni);

        return $codigoCalculado === strtoupper($codigoVerificador);
    }

    /**
     * Calcular código verificador de DNI según algoritmo RENIEC
     *
     * @param string $dni Número de DNI de 8 dígitos
     * @return string Código verificador (1 carácter)
     */
    protected function calcularCodigoVerificador(string $dni): string
    {
        // Tabla de códigos verificadores
        $tabla = [
            0 => '6', 1 => '7', 2 => '8', 3 => '9', 4 => '0',
            5 => '1', 6 => '1', 7 => '2', 8 => '3', 9 => '4',
            10 => '5'
        ];

        // Calcular suma ponderada
        $suma = 0;
        $factores = [3, 2, 7, 6, 5, 4, 3, 2];

        for ($i = 0; $i < 8; $i++) {
            $suma += intval($dni[$i]) * $factores[$i];
        }

        $residuo = $suma % 11;

        return $tabla[$residuo];
    }

    /**
     * Normalizar respuesta de la API PeruDevs a formato estándar
     *
     * Formato de PeruDevs:
     * - id: "12345678"
     * - nombres: "MARIA ISABEL"
     * - apellido_paterno: "JIMENEZ"
     * - apellido_materno: "DIAZ"
     * - nombre_completo: "MARIA ISABEL JIMENEZ DIAZ"
     * - genero: "M"
     * - fecha_nacimiento: "16/11/1994"
     * - codigo_verificacion: "8"
     */
    protected function normalizeResponse(array $data): array
    {
        $normalized = [
            'dni' => $data['id'] ?? $data['dni'] ?? $data['numeroDocumento'] ?? null,
            'nombres' => $data['nombres'] ?? $data['name'] ?? null,
            'apellido_paterno' => $data['apellido_paterno'] ?? $data['apellidoPaterno'] ?? null,
            'apellido_materno' => $data['apellido_materno'] ?? $data['apellidoMaterno'] ?? null,
            'nombre_completo' => $data['nombre_completo'] ?? $data['nombreCompleto'] ?? null,
            'genero' => $data['genero'] ?? $data['sexo'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? $data['fechaNacimiento'] ?? null,
            'codigo_verificacion' => $data['codigo_verificacion'] ?? $data['codigoVerificacion'] ?? null,
            'raw' => $data // Datos originales
        ];

        // Si no hay nombre completo, construirlo
        if (!$normalized['nombre_completo'] && $normalized['nombres'] && $normalized['apellido_paterno']) {
            $normalized['nombre_completo'] = trim(
                $normalized['nombres'] . ' ' .
                $normalized['apellido_paterno'] . ' ' .
                ($normalized['apellido_materno'] ?? '')
            );
        }

        return $normalized;
    }

    /**
     * Limpiar caché de un DNI específico
     */
    public function clearCache(string $dni): void
    {
        Cache::forget("reniec:dni:{$dni}");
    }

    /**
     * Obtener información formateada para registro de usuario
     */
    public function getDatosParaRegistro(string $dni): ?array
    {
        $datos = $this->consultarDni($dni);

        if (!$datos) {
            return null;
        }

        // Formatear nombres: Primera letra en mayúscula, resto en minúscula
        $nombres = $this->formatearNombre($datos['nombres']);
        $apellidoPaterno = $this->formatearNombre($datos['apellido_paterno']);
        $apellidoMaterno = $this->formatearNombre($datos['apellido_materno']);

        return [
            'first_name' => $nombres,
            'last_name' => trim($apellidoPaterno . ' ' . $apellidoMaterno),
            'full_name' => trim($nombres . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno),
        ];
    }

    /**
     * Formatear nombre: Primera letra en mayúscula, resto en minúscula
     */
    protected function formatearNombre(?string $nombre): string
    {
        if (empty($nombre)) {
            return '';
        }

        return mb_convert_case(mb_strtolower($nombre, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Validar datos de registro con RENIEC
     *
     * @param string $dni
     * @param string|null $codigoVerificador
     * @return array ['valid' => bool, 'data' => array|null, 'message' => string]
     */
    public function validarParaRegistro(string $dni, ?string $codigoVerificador = null): array
    {
        try {
            // Si se proporciona código verificador, validarlo primero
            if ($codigoVerificador !== null) {
                if (!$this->validarDniConCodigo($dni, $codigoVerificador)) {
                    return [
                        'valid' => false,
                        'data' => null,
                        'message' => 'El código verificador del DNI no coincide. Verifique los datos en su documento.'
                    ];
                }
            }

            // Consultar datos en RENIEC
            $datos = $this->getDatosParaRegistro($dni);

            if (!$datos) {
                return [
                    'valid' => false,
                    'data' => null,
                    'message' => 'No se encontraron datos para el DNI proporcionado. Verifique el número.'
                ];
            }

            return [
                'valid' => true,
                'data' => $datos,
                'message' => 'DNI validado correctamente'
            ];

        } catch (\Exception $e) {
            Log::error("RENIEC: Error en validación para registro", [
                'dni' => $dni,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }
}
