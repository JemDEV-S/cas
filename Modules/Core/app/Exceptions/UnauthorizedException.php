<?php

namespace Modules\Core\Exceptions;

/**
 * Unauthorized Exception
 *
 * Excepción para accesos no autorizados.
 */
class UnauthorizedException extends CoreException
{
    protected $code = 403;
    protected $message = 'Unauthorized access';

    public function __construct(string $message = null, int $code = null)
    {
        parent::__construct($message ?? $this->message, $code ?? $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'type' => 'unauthorized',
            'code' => $this->getCode(),
        ], $this->getCode());
    }
}
