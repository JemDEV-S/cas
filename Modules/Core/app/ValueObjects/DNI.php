<?php

namespace Modules\Core\ValueObjects;

use InvalidArgumentException;

/**
 * DNI Value Object
 *
 * Representa un DNI peruano válido.
 */
class DNI
{
    /**
     * El valor del DNI.
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
        $this->value = trim($value);
    }

    /**
     * Valida el DNI.
     *
     * @param string $value
     * @return void
     * @throws InvalidArgumentException
     */
    private function validate(string $value): void
    {
        $value = trim($value);

        // Valida que sea exactamente 8 dígitos
        if (!preg_match('/^\d{8}$/', $value)) {
            throw new InvalidArgumentException("El DNI '{$value}' debe tener exactamente 8 dígitos.");
        }
    }

    /**
     * Obtiene el valor del DNI.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Obtiene el DNI con formato (XX.XXX.XXX).
     *
     * @return string
     */
    public function getFormatted(): string
    {
        return substr($this->value, 0, 2) . '.' .
               substr($this->value, 2, 3) . '.' .
               substr($this->value, 5, 3);
    }

    /**
     * Convierte el DNI a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Verifica si dos DNIs son iguales.
     *
     * @param DNI $dni
     * @return bool
     */
    public function equals(DNI $dni): bool
    {
        return $this->value === $dni->getValue();
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
