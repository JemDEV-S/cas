<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Servicio RENIEC Habilitado
    |--------------------------------------------------------------------------
    |
    | Indica si el servicio de validación de DNI con RENIEC está habilitado.
    | Si está deshabilitado, todas las consultas retornarán error 503.
    |
    */
    'enabled' => env('RENIEC_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Configuración de la API
    |--------------------------------------------------------------------------
    |
    | Configuración para conectar con la API de PeruDevs (RENIEC)
    |
    */
    'api' => [
        // URL base de la API
        'url' => env('RENIEC_API_URL', 'https://api.perudevs.com/api/v1'),

        // Token de autenticación (requerido)
        'token' => env('RENIEC_API_TOKEN'),

        // Timeout en segundos para las peticiones HTTP
        'timeout' => (int) env('RENIEC_API_TIMEOUT', 10),

        // Configuración de reintentos
        'retry' => [
            // Número de reintentos en caso de error de red o 5xx
            'times' => (int) env('RENIEC_API_RETRY_TIMES', 3),

            // Tiempo de espera entre reintentos en milisegundos
            'sleep' => (int) env('RENIEC_API_RETRY_SLEEP', 1000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Caché
    |--------------------------------------------------------------------------
    |
    | El caché reduce costos al evitar consultas repetidas a la API.
    | Solo se cachean respuestas exitosas.
    |
    */
    'cache' => [
        // Habilitar o deshabilitar caché
        'enabled' => env('RENIEC_CACHE_ENABLED', true),

        // Tiempo de vida del caché en segundos (por defecto 1 hora)
        'ttl' => (int) env('RENIEC_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Validación
    |--------------------------------------------------------------------------
    |
    | Configuración para la validación de código verificador
    |
    */
    'validation' => [
        'check_digit' => [
            // Habilitar validación de código verificador
            'enabled' => true,

            // Verificar código con API además de cálculo local (doble validación)
            'verify_with_api' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Logging
    |--------------------------------------------------------------------------
    |
    | Cumplimiento con LPDP (Ley de Protección de Datos Personales)
    |
    */
    'logging' => [
        // Enmascarar datos sensibles en logs (DNI: 12345678 -> ****5678)
        'mask_sensitive_data' => env('RENIEC_MASK_LOGS', true),
    ],
];
