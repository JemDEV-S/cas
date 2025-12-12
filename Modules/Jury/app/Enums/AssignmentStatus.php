<?php

namespace Modules\Jury\Enums;

enum AssignmentStatus: string
{
    case ACTIVE = 'ACTIVE';
    case REPLACED = 'REPLACED';
    case EXCUSED = 'EXCUSED';
    case REMOVED = 'REMOVED';
    case SUSPENDED = 'SUSPENDED';

    /**
     * Get display name
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Activo',
            self::REPLACED => 'Reemplazado',
            self::EXCUSED => 'Excusado',
            self::REMOVED => 'Removido',
            self::SUSPENDED => 'Suspendido',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::ACTIVE => 'Asignación activa y vigente',
            self::REPLACED => 'Ha sido reemplazado por otro jurado',
            self::EXCUSED => 'Excusado de la asignación',
            self::REMOVED => 'Removido de la asignación',
            self::SUSPENDED => 'Temporalmente suspendido',
        };
    }

    /**
     * Get badge color
     */
    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'success',
            self::REPLACED => 'warning',
            self::EXCUSED => 'info',
            self::REMOVED => 'danger',
            self::SUSPENDED => 'secondary',
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
        return in_array($this, [self::REPLACED, self::EXCUSED, self::REMOVED]);
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
        return [
            self::REPLACED->value,
            self::EXCUSED->value,
            self::REMOVED->value,
        ];
    }
}