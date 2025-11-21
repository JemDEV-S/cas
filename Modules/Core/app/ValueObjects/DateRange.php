<?php

namespace Modules\Core\ValueObjects;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * DateRange Value Object
 *
 * Representa un rango de fechas válido.
 */
class DateRange
{
    /**
     * La fecha de inicio.
     *
     * @var Carbon
     */
    private Carbon $startDate;

    /**
     * La fecha de fin.
     *
     * @var Carbon
     */
    private Carbon $endDate;

    /**
     * Constructor.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @throws InvalidArgumentException
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->validate($startDate, $endDate);
        $this->startDate = $startDate->copy();
        $this->endDate = $endDate->copy();
    }

    /**
     * Valida el rango de fechas.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     * @throws InvalidArgumentException
     */
    private function validate(Carbon $startDate, Carbon $endDate): void
    {
        if ($startDate->greaterThan($endDate)) {
            throw new InvalidArgumentException("La fecha de inicio no puede ser mayor que la fecha de fin.");
        }
    }

    /**
     * Obtiene la fecha de inicio.
     *
     * @return Carbon
     */
    public function getStartDate(): Carbon
    {
        return $this->startDate->copy();
    }

    /**
     * Obtiene la fecha de fin.
     *
     * @return Carbon
     */
    public function getEndDate(): Carbon
    {
        return $this->endDate->copy();
    }

    /**
     * Obtiene la duración en días.
     *
     * @return int
     */
    public function getDurationInDays(): int
    {
        return $this->startDate->diffInDays($this->endDate);
    }

    /**
     * Obtiene la duración en horas.
     *
     * @return int
     */
    public function getDurationInHours(): int
    {
        return $this->startDate->diffInHours($this->endDate);
    }

    /**
     * Verifica si una fecha está dentro del rango.
     *
     * @param Carbon $date
     * @return bool
     */
    public function contains(Carbon $date): bool
    {
        return $date->between($this->startDate, $this->endDate);
    }

    /**
     * Verifica si el rango se solapa con otro rango.
     *
     * @param DateRange $range
     * @return bool
     */
    public function overlaps(DateRange $range): bool
    {
        return $this->startDate->lessThanOrEqualTo($range->getEndDate()) &&
               $this->endDate->greaterThanOrEqualTo($range->getStartDate());
    }

    /**
     * Verifica si el rango es actual (incluye la fecha de hoy).
     *
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->contains(Carbon::now());
    }

    /**
     * Verifica si el rango está en el futuro.
     *
     * @return bool
     */
    public function isFuture(): bool
    {
        return $this->startDate->isFuture();
    }

    /**
     * Verifica si el rango está en el pasado.
     *
     * @return bool
     */
    public function isPast(): bool
    {
        return $this->endDate->isPast();
    }

    /**
     * Verifica si dos rangos son iguales.
     *
     * @param DateRange $range
     * @return bool
     */
    public function equals(DateRange $range): bool
    {
        return $this->startDate->equalTo($range->getStartDate()) &&
               $this->endDate->equalTo($range->getEndDate());
    }

    /**
     * Obtiene el rango formateado.
     *
     * @param string $format
     * @return string
     */
    public function getFormatted(string $format = 'd/m/Y'): string
    {
        return $this->startDate->format($format) . ' - ' . $this->endDate->format($format);
    }

    /**
     * Convierte el rango a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFormatted();
    }

    /**
     * Crea una instancia desde strings.
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $format
     * @return self
     */
    public static function fromStrings(string $startDate, string $endDate, string $format = 'Y-m-d'): self
    {
        return new self(
            Carbon::createFromFormat($format, $startDate),
            Carbon::createFromFormat($format, $endDate)
        );
    }

    /**
     * Crea una instancia para el mes actual.
     *
     * @return self
     */
    public static function currentMonth(): self
    {
        return new self(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );
    }

    /**
     * Crea una instancia para el año actual.
     *
     * @return self
     */
    public static function currentYear(): self
    {
        return new self(
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );
    }

    /**
     * Crea una instancia desde un número de días.
     *
     * @param int $days
     * @param Carbon|null $startDate
     * @return self
     */
    public static function fromDays(int $days, ?Carbon $startDate = null): self
    {
        $start = $startDate ?? Carbon::now();
        return new self($start, $start->copy()->addDays($days));
    }
}
