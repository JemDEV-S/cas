<?php

namespace Modules\Core\Exceptions;

/**
 * Not Found Exception
 *
 * Excepción para recursos no encontrados.
 */
class NotFoundException extends CoreException
{
    protected $code = 404;
    protected $message = 'Resource not found';

    public function __construct(string $message = null, int $code = null)
    {
        parent::__construct($message ?? $this->message, $code ?? $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'type' => 'not_found',
            'code' => $this->getCode(),
        ], $this->getCode());
    }
}
