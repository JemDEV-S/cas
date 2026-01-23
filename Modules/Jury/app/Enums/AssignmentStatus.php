<?php

namespace Modules\Jury\Enums;

enum AssignmentStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';

    /**
     * Get display name
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::ACTIVE => 'Asignación activa y vigente',
            self::INACTIVE => 'Asignación desactivada',
        };
    }

    /**
     * Get badge color
     */
    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
        };
    }

    /**
     * Check if can evaluate
     */
    public function canEvaluate(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if is inactive
     */
    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
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
     * Get active statuses
     */
    public static function activeStatuses(): array
    {
        return [self::ACTIVE->value];
    }

    /**
     * Get inactive statuses
     */
    public static function inactiveStatuses(): array
    {
        return [self::INACTIVE->value];
    }
}