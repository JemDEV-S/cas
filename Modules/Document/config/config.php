<?php

return [
    'name' => 'Document',

    // Configuración de FIRMA PERÚ
    'firmaperu' => [
        // Ruta al archivo de credenciales fwAuthorization.json
        'credentials_path' => env('FIRMAPERU_CREDENTIALS_PATH', storage_path('app/firmaperu/fwAuthorization.json')),

        // Configuración del Servicio de Sello de Tiempo (TSA)
        'tsa_url' => env('FIRMAPERU_TSA_URL', ''),
        'tsa_user' => env('FIRMAPERU_TSA_USER', ''),
        'tsa_password' => env('FIRMAPERU_TSA_PASSWORD', ''),

        // Puerto local para el servidor FIRMA PERÚ
        'local_port' => env('FIRMAPERU_LOCAL_PORT', 48596),

        // Imagen de sello por defecto
        'default_stamp' => env('FIRMAPERU_DEFAULT_STAMP', 'images/sello-institucional.png'),

        // Configuración de firma por defecto
        'default_signature_style' => env('FIRMAPERU_SIGNATURE_STYLE', 1),
        'default_stamp_text_size' => env('FIRMAPERU_STAMP_TEXT_SIZE', 14),
        'default_stamp_word_wrap' => env('FIRMAPERU_STAMP_WORD_WRAP', 37),

        // Nivel de firma por defecto (B, T, LTA)
        'default_signature_level' => env('FIRMAPERU_SIGNATURE_LEVEL', 'B'),

        // Tema de la interfaz
        'default_theme' => env('FIRMAPERU_THEME', 'claro'),
    ],

    // Configuración de PDFs
    'pdf' => [
        'default_paper_size' => 'A4',
        'default_orientation' => 'portrait',
        'default_margins' => [
            'top' => 20,
            'right' => 15,
            'bottom' => 20,
            'left' => 15,
        ],
    ],

    // Almacenamiento de documentos
    'storage' => [
        'disk' => env('DOCUMENT_STORAGE_DISK', 'private'),
        'path' => env('DOCUMENT_STORAGE_PATH', 'documents'),
    ],

    // Auditoría
    'audit' => [
        'enabled' => env('DOCUMENT_AUDIT_ENABLED', true),
        'retention_days' => env('DOCUMENT_AUDIT_RETENTION_DAYS', 365),
    ],
];
