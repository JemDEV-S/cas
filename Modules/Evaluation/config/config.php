<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Evaluation Module Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración general del módulo de evaluaciones
    |
    */

    'name' => 'Evaluation',

    /*
    |--------------------------------------------------------------------------
    | Deadlines
    |--------------------------------------------------------------------------
    */
    'default_deadline_days' => env('EVALUATION_DEFAULT_DEADLINE_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */
    'allow_modification_after_submit' => env('EVALUATION_ALLOW_MODIFICATION', true),
    'modification_requires_admin' => true,

    /*
    |--------------------------------------------------------------------------
    | Scoring
    |--------------------------------------------------------------------------
    */
    'auto_calculate_weights' => true,
    'require_all_comments' => false, // Si true, todos los criterios requieren comentarios
    'decimal_places' => 2,

    /*
    |--------------------------------------------------------------------------
    | Assignments
    |--------------------------------------------------------------------------
    */
    'auto_assignment' => [
        'enabled' => true,
        'algorithm' => 'round_robin', // round_robin, least_loaded, random
        'check_conflicts' => true,
        'max_assignments_per_evaluator' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'on_assignment' => true,
        'on_deadline_approaching' => true,
        'on_evaluation_modified' => true,
        'deadline_warning_days' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */
    'history' => [
        'enabled' => true,
        'track_ip' => true,
        'track_user_agent' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'require_all_criteria' => true, // Todos los criterios deben ser calificados antes de enviar
        'allow_partial_save' => true,    // Permitir guardar borradores parciales
    ],
];