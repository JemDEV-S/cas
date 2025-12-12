<?php

namespace Modules\Jury\Enums;

enum ConflictSeverity: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
    case CRITICAL = 'CRITICAL';

    /**
     * Get display name
     */
    public function label(): string
    {
        return match($this) {
            self::LOW => 'Baja',
            self::MEDIUM => 'Media',
            self::HIGH => 'Alta',
            self::CRITICAL => 'Crítica',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::LOW => 'Conflicto menor que puede gestionarse',
            self::MEDIUM => 'Conflicto que requiere evaluación',
            self::HIGH => 'Conflicto significativo que requiere acción',
            self::CRITICAL => 'Conflicto grave que impide la evaluación',
        };
    }

    /**
     * Get badge color
     */
    public function color(): string
    {
        return match($this) {
            self::LOW => 'info',
            self::MEDIUM => 'warning',
            self::HIGH => 'orange',
            self::CRITICAL => 'danger',
        };
    }

    /**
     * Get priority/weight
     */
    public function priority(): int
    {
        return match($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::CRITICAL => 4,
        };
    }

    /**
     * Requires immediate action
     */
    public function requiresImmediateAction(): bool
    {
        return in_array($this, [self::HIGH, self::CRITICAL]);
    }

    /**
     * Get all values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all options for select
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}