<?php

namespace Modules\Core\ValueObjects;

use InvalidArgumentException;

class ExperienceDuration
{
    public readonly int $years;
    public readonly int $months;

    // Constructor privado para forzar el uso de métodos estáticos (Factory methods)
    private function __construct(int $years, int $months)
    {
        if ($years < 0 || $months < 0) {
            throw new InvalidArgumentException("La experiencia no puede ser negativa.");
        }
        
        // Normalizar: si mandan 0 años y 14 meses -> 1 año y 2 meses
        $additionalYears = intdiv($months, 12);
        $remainingMonths = $months % 12;

        $this->years = $years + $additionalYears;
        $this->months = $remainingMonths;
    }

    // Método 1: Crear desde el decimal de la BD (ej: 2.5)
    public static function fromDecimal(float $totalYears): self
    {
        $years = (int) floor($totalYears);
        // La parte decimal (0.5) se multiplica por 12 y se redondea
        $months = (int) round(($totalYears - $years) * 12);

        return new self($years, $months);
    }

    // Método 2: Crear desde años y meses (si lo necesitas para formularios)
    public static function fromParts(int $years, int $months): self
    {
        return new self($years, $months);
    }

    // Para guardar en BD: Convertir de vuelta a decimal (ej: 2.5)
    public function toDecimal(): float
    {
        // 6 meses / 12 = 0.5
        $decimalPart = round($this->months / 12, 2); 
        return $this->years + $decimalPart;
    }

    // La representación humana que buscas
    public function toHuman(): string
    {
        $parts = [];

        if ($this->years > 0) {
            $parts[] = $this->years . ' ' . ($this->years === 1 ? 'año' : 'años');
        }

        if ($this->months > 0) {
            $parts[] = $this->months . ' ' . ($this->months === 1 ? 'mes' : 'meses');
        }

        if (empty($parts)) {
            return 'Sin experiencia';
        }

        return implode(' y ', $parts);
    }

    public function __toString(): string
    {
        return $this->toHuman();
    }
}