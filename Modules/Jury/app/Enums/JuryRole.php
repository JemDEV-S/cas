<?php

namespace Modules\Jury\Enums;

enum JuryRole: string
{
    case PRESIDENTE = 'PRESIDENTE';
    case SECRETARIO = 'SECRETARIO';
    case VOCAL = 'VOCAL';
    case MIEMBRO = 'MIEMBRO';

    /**
     * Get display name
     */
    public function label(): string
    {
        return match($this) {
            self::PRESIDENTE => 'Presidente',
            self::SECRETARIO => 'Secretario',
            self::VOCAL => 'Vocal',
            self::MIEMBRO => 'Miembro',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::PRESIDENTE => 'Presidente del jurado evaluador',
            self::SECRETARIO => 'Secretario del jurado evaluador',
            self::VOCAL => 'Vocal del jurado evaluador',
            self::MIEMBRO => 'Miembro del jurado sin rol específico',
        };
    }

    /**
     * Get badge color
     */
    public function color(): string
    {
        return match($this) {
            self::PRESIDENTE => 'danger',
            self::SECRETARIO => 'warning',
            self::VOCAL => 'info',
            self::MIEMBRO => 'secondary',
        };
    }

    /**
     * Get order/priority
     */
    public function order(): int
    {
        return match($this) {
            self::PRESIDENTE => 1,
            self::SECRETARIO => 2,
            self::VOCAL => 3,
            self::MIEMBRO => 4,
        };
    }

    /**
     * Check if role has authority
     */
    public function hasAuthority(): bool
    {
        return in_array($this, [self::PRESIDENTE, self::SECRETARIO]);
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

    /**
     * Get options ordered by priority
     */
    public static function orderedOptions(): array
    {
        return collect(self::cases())
            ->sortBy(fn($case) => $case->order())
            ->mapWithKeys(fn($case) => [
                $case->value => $case->label()
            ])->toArray();
    }
}