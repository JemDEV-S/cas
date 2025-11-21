<?php

namespace Modules\Core\ValueObjects;

use InvalidArgumentException;

/**
 * Money Value Object
 *
 * Representa un valor monetario con su moneda.
 */
class Money
{
    /**
     * El monto.
     *
     * @var float
     */
    private float $amount;

    /**
     * La moneda (ISO 4217).
     *
     * @var string
     */
    private string $currency;

    /**
     * Constructor.
     *
     * @param float $amount
     * @param string $currency
     * @throws InvalidArgumentException
     */
    public function __construct(float $amount, string $currency = 'PEN')
    {
        $this->validate($amount, $currency);
        $this->amount = round($amount, 2);
        $this->currency = strtoupper($currency);
    }

    /**
     * Valida el monto y la moneda.
     *
     * @param float $amount
     * @param string $currency
     * @return void
     * @throws InvalidArgumentException
     */
    private function validate(float $amount, string $currency): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("El monto no puede ser negativo.");
        }

        $validCurrencies = ['PEN', 'USD', 'EUR'];
        if (!in_array(strtoupper($currency), $validCurrencies)) {
            throw new InvalidArgumentException("La moneda '{$currency}' no es válida.");
        }
    }

    /**
     * Obtiene el monto.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Obtiene la moneda.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Suma dos montos (deben ser de la misma moneda).
     *
     * @param Money $money
     * @return Money
     * @throws InvalidArgumentException
     */
    public function add(Money $money): Money
    {
        $this->ensureSameCurrency($money);
        return new self($this->amount + $money->getAmount(), $this->currency);
    }

    /**
     * Resta dos montos (deben ser de la misma moneda).
     *
     * @param Money $money
     * @return Money
     * @throws InvalidArgumentException
     */
    public function subtract(Money $money): Money
    {
        $this->ensureSameCurrency($money);
        $result = $this->amount - $money->getAmount();

        if ($result < 0) {
            throw new InvalidArgumentException("El resultado no puede ser negativo.");
        }

        return new self($result, $this->currency);
    }

    /**
     * Multiplica el monto por un factor.
     *
     * @param float $multiplier
     * @return Money
     */
    public function multiply(float $multiplier): Money
    {
        return new self($this->amount * $multiplier, $this->currency);
    }

    /**
     * Divide el monto por un divisor.
     *
     * @param float $divisor
     * @return Money
     * @throws InvalidArgumentException
     */
    public function divide(float $divisor): Money
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException("No se puede dividir por cero.");
        }

        return new self($this->amount / $divisor, $this->currency);
    }

    /**
     * Verifica si dos montos son iguales.
     *
     * @param Money $money
     * @return bool
     */
    public function equals(Money $money): bool
    {
        return $this->amount === $money->getAmount() &&
               $this->currency === $money->getCurrency();
    }

    /**
     * Verifica si este monto es mayor que otro.
     *
     * @param Money $money
     * @return bool
     */
    public function greaterThan(Money $money): bool
    {
        $this->ensureSameCurrency($money);
        return $this->amount > $money->getAmount();
    }

    /**
     * Verifica si este monto es menor que otro.
     *
     * @param Money $money
     * @return bool
     */
    public function lessThan(Money $money): bool
    {
        $this->ensureSameCurrency($money);
        return $this->amount < $money->getAmount();
    }

    /**
     * Asegura que ambos montos tengan la misma moneda.
     *
     * @param Money $money
     * @return void
     * @throws InvalidArgumentException
     */
    private function ensureSameCurrency(Money $money): void
    {
        if ($this->currency !== $money->getCurrency()) {
            throw new InvalidArgumentException("Las monedas deben ser las mismas.");
        }
    }

    /**
     * Obtiene el símbolo de la moneda.
     *
     * @return string
     */
    public function getCurrencySymbol(): string
    {
        $symbols = [
            'PEN' => 'S/',
            'USD' => '$',
            'EUR' => '€',
        ];

        return $symbols[$this->currency] ?? $this->currency;
    }

    /**
     * Obtiene el monto formateado.
     *
     * @return string
     */
    public function getFormatted(): string
    {
        return $this->getCurrencySymbol() . ' ' . number_format($this->amount, 2, '.', ',');
    }

    /**
     * Convierte el monto a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFormatted();
    }

    /**
     * Crea una instancia desde valores primitivos.
     *
     * @param float $amount
     * @param string $currency
     * @return self
     */
    public static function from(float $amount, string $currency = 'PEN'): self
    {
        return new self($amount, $currency);
    }
}
