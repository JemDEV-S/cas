<?php

namespace Modules\Core\Exceptions;

use Exception;

/**
 * Core Exception
 *
 * Excepción base para el módulo Core.
 */
class CoreException extends Exception
{
    protected $code = 500;
    protected $message = 'Core module exception';

    public function __construct(string $message = null, int $code = null, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code ?? $this->code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
        ], $this->getCode());
    }
}
