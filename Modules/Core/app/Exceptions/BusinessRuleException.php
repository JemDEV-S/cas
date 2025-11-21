<?php

namespace Modules\Core\Exceptions;

/**
 * Business Rule Exception
 *
 * Excepción para violaciones de reglas de negocio.
 */
class BusinessRuleException extends CoreException
{
    protected $code = 400;
    protected $message = 'Business rule violation';

    public function __construct(string $message = null, int $code = null)
    {
        parent::__construct($message ?? $this->message, $code ?? $this->code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'type' => 'business_rule_violation',
            'code' => $this->getCode(),
        ], $this->getCode());
    }
}
