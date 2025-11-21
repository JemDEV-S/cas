<?php

namespace Modules\Core\ValueObjects;

use InvalidArgumentException;

/**
 * Email Value Object
 *
 * Representa una dirección de email válida.
 */
class Email
{
    /**
     * El valor del email.
     *
     * @var string
     */
    private string $value;

    /**
     * Constructor.
     *
     * @param string $value
     * @throws InvalidArgumentException
     */
    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = strtolower(trim($value));
    }

    /**
     * Valida el email.
     *
     * @param string $value
     * @return void
     * @throws InvalidArgumentException
     */
    private function validate(string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El email '{$value}' no es válido.");
        }
    }

    /**
     * Obtiene el valor del email.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Obtiene el dominio del email.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return substr(strrchr($this->value, '@'), 1);
    }

    /**
     * Obtiene el nombre local del email.
     *
     * @return string
     */
    public function getLocalPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    /**
     * Convierte el email a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Verifica si dos emails son iguales.
     *
     * @param Email $email
     * @return bool
     */
    public function equals(Email $email): bool
    {
        return $this->value === $email->getValue();
    }

    /**
     * Crea una instancia desde un string.
     *
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
