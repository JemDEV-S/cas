<?php

namespace Modules\Core\ValueObjects;

use InvalidArgumentException;

/**
 * PhoneNumber Value Object
 *
 * Representa un número de teléfono peruano válido.
 */
class PhoneNumber
{
    /**
     * El valor del número de teléfono.
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
        $this->value = $this->normalize($value);
    }

    /**
     * Valida el número de teléfono.
     *
     * @param string $value
     * @return void
     * @throws InvalidArgumentException
     */
    private function validate(string $value): void
    {
        $normalized = $this->normalize($value);

        // Valida formato peruano: 9 dígitos comenzando con 9
        if (!preg_match('/^9\d{8}$/', $normalized)) {
            throw new InvalidArgumentException("El número de teléfono '{$value}' no es válido.");
        }
    }

    /**
     * Normaliza el número de teléfono.
     *
     * @param string $value
     * @return string
     */
    private function normalize(string $value): string
    {
        // Remueve espacios, guiones y paréntesis
        $value = preg_replace('/[\s\-\(\)]/', '', $value);

        // Remueve el código de país si está presente
        $value = preg_replace('/^\+?51/', '', $value);

        return $value;
    }

    /**
     * Obtiene el valor del número de teléfono.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Obtiene el número con formato internacional.
     *
     * @return string
     */
    public function getInternational(): string
    {
        return '+51' . $this->value;
    }

    /**
     * Obtiene el número con formato legible.
     *
     * @return string
     */
    public function getFormatted(): string
    {
        return substr($this->value, 0, 3) . ' ' . substr($this->value, 3, 3) . ' ' . substr($this->value, 6);
    }

    /**
     * Convierte el número a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Verifica si dos números son iguales.
     *
     * @param PhoneNumber $phone
     * @return bool
     */
    public function equals(PhoneNumber $phone): bool
    {
        return $this->value === $phone->getValue();
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
