<?php

namespace Modules\Jury\Enums;

enum ConflictStatus: string
{
    case REPORTED = 'REPORTED';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case CONFIRMED = 'CONFIRMED';
    case DISMISSED = 'DISMISSED';
    case RESOLVED = 'RESOLVED';

    /**
     * Get display name
     */
    public function label(): string
    {
        return match($this) {
            self::REPORTED => 'Reportado',
            self::UNDER_REVIEW => 'En Revisión',
            self::CONFIRMED => 'Confirmado',
            self::DISMISSED => 'Desestimado',
            self::RESOLVED => 'Resuelto',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::REPORTED => 'Conflicto reportado, pendiente de revisión',
            self::UNDER_REVIEW => 'En proceso de revisión y análisis',
            self::CONFIRMED => 'Conflicto confirmado, requiere acción',
            self::DISMISSED => 'Conflicto desestimado, no requiere acción',
            self::RESOLVED => 'Conflicto resuelto con acción tomada',
        };
    }

    /**
     * Get badge color
     */
    public function color(): string
    {
        return match($this) {
            self::REPORTED => 'warning',
            self::UNDER_REVIEW => 'info',
            self::CONFIRMED => 'danger',
            self::DISMISSED => 'secondary',
            self::RESOLVED => 'success',
        };
    }

    /**
     * Check if pending action
     */
    public function isPending(): bool
    {
        return in_array($this, [self::REPORTED, self::UNDER_REVIEW, self::CONFIRMED]);
    }

    /**
     * Check if closed
     */
    public function isClosed(): bool
    {
        return in_array($this, [self::DISMISSED, self::RESOLVED]);
    }

    /**
     * Can transition to status
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::REPORTED => in_array($newStatus, [self::UNDER_REVIEW, self::DISMISSED]),
            self::UNDER_REVIEW => in_array($newStatus, [self::CONFIRMED, self::DISMISSED]),
            self::CONFIRMED => in_array($newStatus, [self::RESOLVED]),
            self::DISMISSED => false,
            self::RESOLVED => false,
        };
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
     * Get pending statuses
     */
    public static function pendingStatuses(): array
    {
        return [
            self::REPORTED->value,
            self::UNDER_REVIEW->value,
            self::CONFIRMED->value,
        ];
    }

    /**
     * Get closed statuses
     */
    public static function closedStatuses(): array
    {
        return [
            self::DISMISSED->value,
            self::RESOLVED->value,
        ];
    }
}