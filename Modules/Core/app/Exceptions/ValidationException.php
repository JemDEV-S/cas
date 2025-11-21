<?php

namespace Modules\Core\Exceptions;

/**
 * Validation Exception
 *
 * Excepción para errores de validación.
 */
class ValidationException extends CoreException
{
    protected $code = 422;
    protected $message = 'Validation error';

    protected array $errors = [];

    public function __construct(string $message = null, array $errors = [], int $code = null)
    {
        $this->errors = $errors;
        parent::__construct($message ?? $this->message, $code ?? $this->code);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errors,
            'code' => $this->getCode(),
        ], $this->getCode());
    }
}
